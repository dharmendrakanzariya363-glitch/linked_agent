<?php

namespace App\Http\Controllers;

use App\Enums\CampaignStatus;
use App\Exceptions\LinkedInException;
use App\Http\Resources\LinkedInAccountResource;
use App\Models\LinkedInAccount;
use App\Services\LinkedIn\LinkedInService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class LinkedInController extends Controller
{
    public function __construct(private LinkedInService $linkedin) {}

    public function index(Request $request): Response
    {
        return Inertia::render('linkedin/index', [
            'accounts' => LinkedInAccountResource::collection(
                $request->user()->linkedinAccounts()->latest()->get()
            ),
        ]);
    }

    public function connect(Request $request): RedirectResponse
    {
        $state = $this->linkedin->createState();
        $request->session()->put('linkedin_oauth_state', $state);

        return redirect()->away($this->linkedin->authorizationUrl($state));
    }

    public function callback(Request $request): RedirectResponse
    {
        $expected = (string) $request->session()->pull('linkedin_oauth_state');

        if ($request->string('error')->isNotEmpty()) {
            return redirect()->route('linkedin.index')
                ->with('error', LinkedInException::denied()->getMessage());
        }

        if ($expected === '' || $expected !== $request->string('state')->toString()) {
            return redirect()->route('linkedin.index')
                ->with('error', LinkedInException::invalidState()->getMessage());
        }

        try {
            $this->linkedin->connect($request->user(), $request->string('code')->toString());
        } catch (LinkedInException $e) {
            return redirect()->route('linkedin.index')->with('error', $e->getMessage());
        }

        return redirect()->route('linkedin.index')->with('success', 'LinkedIn connected.');
    }

    public function disconnect(Request $request, LinkedInAccount $linkedinAccount): RedirectResponse
    {
        $this->authorize('delete', $linkedinAccount);

        $this->linkedin->disconnect($linkedinAccount);

        $linkedinAccount->campaigns()
            ->where('status', CampaignStatus::Active)
            ->update(['status' => CampaignStatus::Paused]);

        return back()->with('success', 'LinkedIn disconnected. Active campaigns using this account were paused.');
    }
}
