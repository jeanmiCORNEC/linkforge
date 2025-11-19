<?php

namespace App\Http\Controllers\Profile;

use App\Http\Requests\ProfileUpdateRequest;
use App\Http\Controllers\Controller;
use App\Support\Features\FeatureManager;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Inertia\Response;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): Response
    {
        $user = $request->user();
        $featureScope = FeatureManager::for($user);

        $integrations = $user->affiliateIntegrations()
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn ($integration) => [
                'id'           => $integration->id,
                'platform'     => $integration->platform,
                'label'        => $integration->label,
                'status'       => $integration->status,
                'statusLabel'  => ucfirst($integration->status),
            ]);

        return Inertia::render('Profile/Edit', [
            'mustVerifyEmail'       => $user instanceof MustVerifyEmail,
            'status'                => session('status'),
            'plan'                  => $user->plan,
            'integrations'          => $integrations,
            'platforms'             => config('affiliate.platforms'),
            'canManageIntegrations' => $featureScope->allows('integrations.manage'),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $request->user()->fill($request->validated());

        if ($request->user()->isDirty('email')) {
            $request->user()->email_verified_at = null;
        }

        $request->user()->save();

        return Redirect::route('profile.edit');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validate([
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
