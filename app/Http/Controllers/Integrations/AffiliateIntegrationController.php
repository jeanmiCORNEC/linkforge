<?php

namespace App\Http\Controllers\Integrations;

use App\Http\Controllers\Controller;
use App\Models\AffiliateIntegration;
use App\Support\Features\FeatureManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AffiliateIntegrationController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (! FeatureManager::for($user)->allows('integrations.manage')) {
            abort(403);
        }

        $platformIds = collect(config('affiliate.platforms'))
            ->pluck('id')
            ->all();

        $data = $request->validate([
            'platform' => ['required', Rule::in($platformIds)],
            'label'    => ['required', 'string', 'max:190'],
            'credentials' => ['required', 'array'],
        ]);

        $user->affiliateIntegrations()->create([
            'platform'    => $data['platform'],
            'label'       => $data['label'],
            'status'      => 'pending',
            'credentials' => $data['credentials'],
        ]);

        return back()->with('status', 'integration-added');
    }

    public function destroy(Request $request, AffiliateIntegration $integration): RedirectResponse
    {
        $user = $request->user();

        if ($integration->user_id !== $user->id) {
            abort(403);
        }

        $integration->delete();

        return back()->with('status', 'integration-removed');
    }
}
