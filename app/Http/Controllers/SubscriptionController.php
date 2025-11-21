<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Cashier\Exceptions\IncompletePayment;

class SubscriptionController extends Controller
{
    /**
     * Create a checkout session for the Pro plan.
     */
    public function checkout(Request $request)
    {
        $user = $request->user();

        if ($user->subscribed('pro')) {
            return redirect()->route('profile.edit')->with('status', 'already-subscribed');
        }

        $priceId = config('services.stripe.price_pro');

        if (empty($priceId)) {
            return redirect()->route('profile.edit')->with('error', 'Stripe configuration error: Price ID is missing.');
        }

        if (! str_starts_with($priceId, 'price_') && ! str_starts_with($priceId, 'plan_')) {
            return redirect()->route('profile.edit')->with('error', "Stripe configuration error: Invalid Price ID format. It should start with 'price_' or 'plan_', but got '{$priceId}'. You likely used the Product ID by mistake.");
        }

        try {
            return $user->newSubscription('pro', $priceId)
                ->checkout([
                    'success_url' => route('profile.edit', ['success' => 'true']),
                    'cancel_url' => route('profile.edit', ['cancel' => 'true']),
                ]);
        } catch (IncompletePayment $exception) {
            return redirect()->route('cashier.payment', [$exception->payment->id, 'redirect' => route('profile.edit')]);
        }
    }

    /**
     * Redirect to the Stripe Customer Portal.
     */
    public function portal(Request $request)
    {
        return $request->user()->redirectToBillingPortal(route('profile.edit'));
    }
}
