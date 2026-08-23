<?php

namespace App\Http\Controllers;

use App\Enums\PostStatus;
use App\Http\Requests\AiEditPostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Jobs\EditPostImageWithAiJob;
use App\Jobs\EditPostWithAiJob;
use App\Jobs\RegeneratePostDescriptionJob;
use App\Jobs\RegeneratePostImageJob;
use App\Models\Post;
use App\Services\Post\PostGenerationService;
use App\Services\Post\PostPublishingService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PostController extends Controller
{
    public function __construct(
        private PostGenerationService $generator,
        private PostPublishingService $publisher,
    ) {}

    public function index(Request $request): Response
    {
        $posts = $request->user()
            ->posts()
            ->with(['campaign', 'topic', 'currentVersion.image'])
            ->latest('scheduled_for')
            ->paginate(12)
            ->withQueryString();

        return Inertia::render('posts/index', [
            'posts' => PostResource::collection($posts),
        ]);
    }

    public function show(Post $post): Response
    {
        $this->authorize('view', $post);

        $post->load([
            'campaign',
            'topic',
            'currentVersion.image',
            'versions' => fn ($query) => $query->with('image')->latest('version_number')->limit(20),
        ]);

        return Inertia::render('posts/show', [
            'post' => new PostResource($post),
        ]);
    }

    public function update(UpdatePostRequest $request, Post $post): RedirectResponse
    {
        $this->generator->saveManualEdit($post, $request->validated('description'));

        return back()->with('success', 'Saved a new version.');
    }

    public function regenerate(Post $post): RedirectResponse
    {
        $this->authorize('update', $post);
        $this->generator->assertEditable($post);
        $post->forceFill(['status' => PostStatus::Generating])->save();
        RegeneratePostDescriptionJob::dispatch($post->id);

        return back()->with('success', 'Regenerating description.');
    }

    public function regenerateImage(Post $post): RedirectResponse
    {
        $this->authorize('update', $post);
        $this->generator->assertEditable($post);
        $post->forceFill(['status' => PostStatus::Generating])->save();
        RegeneratePostImageJob::dispatch($post->id);

        return back()->with('success', 'Regenerating image.');
    }

    public function aiEdit(AiEditPostRequest $request, Post $post): RedirectResponse
    {
        $this->generator->assertEditable($post);
        $post->forceFill(['status' => PostStatus::Generating])->save();
        EditPostWithAiJob::dispatch($post->id, $request->validated('instruction'));

        return back()->with('success', 'AI is rewriting this post.');
    }

    public function aiEditImage(AiEditPostRequest $request, Post $post): RedirectResponse
    {
        $this->authorize('update', $post);
        $this->generator->assertEditable($post);
        $post->forceFill(['status' => PostStatus::Generating])->save();
        EditPostImageWithAiJob::dispatch($post->id, $request->validated('instruction'));

        return back()->with('success', 'AI is editing the image.');
    }

    public function approve(Post $post): RedirectResponse
    {
        $this->authorize('approve', $post);
        $this->publisher->approve($post);

        return back()->with('success', 'Approved. Publishing to LinkedIn.');
    }

    public function retry(Post $post): RedirectResponse
    {
        $this->authorize('publish', $post);
        $this->publisher->retry($post);

        return back()->with('success', 'Retrying publish.');
    }
}
