<?php

namespace App\Services\LinkedIn;

use App\Data\LinkedInProfile;
use App\Exceptions\LinkedInException;
use App\Models\LinkedInAccount;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Throwable;

class LinkedInService
{
    public function authorizationUrl(string $state): string
    {
        $query = http_build_query([
            'response_type' => 'code',
            'client_id' => (string) config('linkedin.client_id'),
            'redirect_uri' => (string) config('linkedin.redirect'),
            'state' => $state,
            'scope' => implode(' ', config('linkedin.scopes')),
        ]);

        return config('linkedin.authorize_url').'?'.$query;
    }

    public function makeState(): string
    {
        return Str::random(40);
    }

    public function createState(): string
    {
        return $this->makeState();
    }

    public function exchangeCode(string $code): LinkedInProfile
    {
        $tokenResponse = Http::asForm()
            ->acceptJson()
            ->post((string) config('linkedin.token_url'), [
                'grant_type' => 'authorization_code',
                'code' => $code,
                'redirect_uri' => (string) config('linkedin.redirect'),
                'client_id' => (string) config('linkedin.client_id'),
                'client_secret' => (string) config('linkedin.client_secret'),
            ]);

        if ($tokenResponse->failed()) {
            Log::warning('LinkedIn token exchange failed.', [
                'status' => $tokenResponse->status(),
            ]);

            throw LinkedInException::tokenExchangeFailed();
        }

        $accessToken = (string) $tokenResponse->json('access_token');
        $refreshToken = $tokenResponse->json('refresh_token');
        $expiresIn = (int) $tokenResponse->json('expires_in', 3600);
        $scope = (string) $tokenResponse->json('scope', implode(' ', config('linkedin.scopes')));

        if ($accessToken === '') {
            throw LinkedInException::tokenExchangeFailed();
        }

        $profile = $this->fetchUserInfo($accessToken);

        return new LinkedInProfile(
            id: (string) ($profile['sub'] ?? ''),
            name: trim((string) ($profile['name'] ?? $profile['given_name'] ?? 'LinkedIn user')),
            email: $profile['email'] ?? null,
            headline: $profile['headline'] ?? null,
            profileUrl: $profile['profile'] ?? $profile['picture'] ?? null,
            avatarUrl: $profile['picture'] ?? null,
            accessToken: $accessToken,
            refreshToken: is_string($refreshToken) ? $refreshToken : null,
            expiresAt: now()->addSeconds(max($expiresIn, 60)),
            scopes: array_values(array_filter(explode(' ', $scope))),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function fetchUserInfo(string $accessToken): array
    {
        $response = Http::withToken($accessToken)
            ->acceptJson()
            ->get(rtrim((string) config('linkedin.api_base_url'), '/').'/v2/userinfo');

        if ($response->failed()) {
            Log::warning('LinkedIn profile lookup failed.', ['status' => $response->status()]);

            throw LinkedInException::tokenExchangeFailed();
        }

        /** @var array<string, mixed> $data */
        $data = $response->json() ?? [];

        return $data;
    }

    public function connect(User $user, string $code): LinkedInAccount
    {
        $profile = $this->exchangeCode($code);

        if ($profile->id === '') {
            throw LinkedInException::tokenExchangeFailed();
        }

        return LinkedInAccount::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'linkedin_id' => $profile->id,
            ],
            [
                'name' => $profile->name,
                'email' => $profile->email,
                'headline' => $profile->headline,
                'profile_url' => $profile->profileUrl,
                'avatar_url' => $profile->avatarUrl,
                'access_token' => $profile->accessToken,
                'refresh_token' => $profile->refreshToken,
                'token_expires_at' => $profile->expiresAt,
                'scopes' => $profile->scopes,
                'disconnected_at' => null,
            ],
        );
    }

    public function disconnect(LinkedInAccount $account): void
    {
        $account->forceFill([
            'access_token' => null,
            'refresh_token' => null,
            'disconnected_at' => now(),
        ])->save();
    }

    public function validAccessToken(LinkedInAccount $account): string
    {
        if (! $account->isConnected()) {
            throw LinkedInException::notConnected();
        }

        if ($account->tokenIsExpired()) {
            $this->refreshToken($account);
            $account->refresh();
        }

        $token = (string) $account->access_token;

        if ($token === '') {
            throw LinkedInException::tokenExpired();
        }

        return $token;
    }

    public function refreshToken(LinkedInAccount $account): void
    {
        if (blank($account->refresh_token)) {
            $account->forceFill(['disconnected_at' => now()])->save();

            throw LinkedInException::tokenExpired();
        }

        $response = Http::asForm()
            ->acceptJson()
            ->post((string) config('linkedin.token_url'), [
                'grant_type' => 'refresh_token',
                'refresh_token' => $account->refresh_token,
                'client_id' => (string) config('linkedin.client_id'),
                'client_secret' => (string) config('linkedin.client_secret'),
            ]);

        if ($response->failed()) {
            Log::warning('LinkedIn token refresh failed.', [
                'account_id' => $account->id,
                'status' => $response->status(),
            ]);

            $account->forceFill(['disconnected_at' => now()])->save();

            throw LinkedInException::tokenExpired();
        }

        $account->forceFill([
            'access_token' => $response->json('access_token'),
            'refresh_token' => $response->json('refresh_token') ?: $account->refresh_token,
            'token_expires_at' => now()->addSeconds((int) $response->json('expires_in', 3600)),
            'disconnected_at' => null,
        ])->save();
    }

    /**
     * @return array{id: string, url: string|null}
     */
    public function publish(Post $post): array
    {
        $account = $post->linkedinAccount;
        $version = $post->currentVersion;

        if ($account === null || ! $account->isConnected()) {
            throw LinkedInException::notConnected();
        }

        if ($version === null || blank($version->description)) {
            throw LinkedInException::publishFailed('This post does not have publishable content yet.');
        }

        $token = $this->validAccessToken($account);
        $author = $account->personUrn();
        $payload = [
            'author' => $author,
            'commentary' => $this->truncateCommentary($version->description),
            'visibility' => 'PUBLIC',
            'distribution' => [
                'feedDistribution' => 'MAIN_FEED',
                'targetEntities' => [],
                'thirdPartyDistributionChannels' => [],
            ],
            'lifecycleState' => 'PUBLISHED',
            'isReshareDisabledByAuthor' => false,
        ];

        if ($post->campaign->requiresImage()) {
            $image = $version->image;

            if ($image === null) {
                throw LinkedInException::publishFailed('This campaign requires an image before publishing.');
            }

            $payload['content'] = [
                'media' => [
                    'id' => $this->uploadImage($account, $image->disk, $image->path),
                ],
            ];
        }

        $response = $this->rest($token)->post('/rest/posts', $payload);

        if ($response->failed()) {
            Log::warning('LinkedIn publish failed.', [
                'post_id' => $post->id,
                'status' => $response->status(),
            ]);

            throw LinkedInException::publishFailed($this->friendlyPublishError($response));
        }

        $postId = (string) ($response->header('x-restli-id') ?: $response->json('id') ?: '');

        return [
            'id' => $postId,
            'url' => $postId !== '' ? 'https://www.linkedin.com/feed/update/'.$postId : null,
        ];
    }

    public function uploadImage(LinkedInAccount $account, string $disk, string $path): string
    {
        $token = $this->validAccessToken($account);
        $init = $this->rest($token)->post('/rest/images?action=initializeUpload', [
            'initializeUploadRequest' => [
                'owner' => $account->personUrn(),
            ],
        ]);

        if ($init->failed()) {
            Log::warning('LinkedIn image upload init failed.', ['status' => $init->status()]);

            throw LinkedInException::publishFailed('LinkedIn could not start the image upload.');
        }

        $uploadUrl = (string) $init->json('value.uploadUrl');
        $imageUrn = (string) $init->json('value.image');

        if ($uploadUrl === '' || $imageUrn === '') {
            throw LinkedInException::publishFailed('LinkedIn did not return an image upload URL.');
        }

        $binary = Storage::disk($disk)->get($path);

        if ($binary === null) {
            throw LinkedInException::publishFailed('The generated image file is missing.');
        }

        $upload = Http::withToken($token)
            ->withHeaders(['Content-Type' => 'application/octet-stream'])
            ->withBody($binary, 'application/octet-stream')
            ->put($uploadUrl);

        if ($upload->failed()) {
            Log::warning('LinkedIn image binary upload failed.', ['status' => $upload->status()]);

            throw LinkedInException::publishFailed('LinkedIn could not upload the image.');
        }

        return $imageUrn;
    }

    private function rest(string $token): PendingRequest
    {
        return Http::withToken($token)
            ->acceptJson()
            ->asJson()
            ->withHeaders([
                'LinkedIn-Version' => (string) config('linkedin.api_version'),
                'X-Restli-Protocol-Version' => '2.0.0',
            ])
            ->baseUrl(rtrim((string) config('linkedin.api_base_url'), '/'));
    }

    private function truncateCommentary(string $text): string
    {
        if (mb_strlen($text) <= 3000) {
            return $text;
        }

        return rtrim(mb_substr($text, 0, 2997)).'...';
    }

    private function friendlyPublishError(Response $response): string
    {
        $serviceError = $response->json('message') ?? $response->json('error_description');

        return is_string($serviceError) && $serviceError !== ''
            ? 'LinkedIn rejected the post. Please review the content and try again.'
            : 'LinkedIn could not publish this post. Please try again.';
    }

    public function safeDisconnectMessage(Throwable $e): string
    {
        return $e instanceof LinkedInException
            ? $e->getMessage()
            : 'We could not update the LinkedIn connection. Please try again.';
    }
}
