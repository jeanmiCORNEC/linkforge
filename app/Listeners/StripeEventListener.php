<?php

namespace App\Listeners;

use Laravel\Cashier\Events\WebhookReceived;
use Illuminate\Support\Facades\Log;

class StripeEventListener
{
    /**
     * Handle the event.
     */
    public function handle(WebhookReceived $event): void
    {
        $payload = $event->payload;
        $type = $payload['type'];

        if ($type === 'customer.subscription.created' || $type === 'customer.subscription.updated') {
            $this->handleSubscriptionUpdated($payload);
        }

        if ($type === 'customer.subscription.deleted') {
            $this->handleSubscriptionDeleted($payload);
        }
    }

    protected function handleSubscriptionUpdated(array $payload)
    {
        $object = $payload['data']['object'];
        $user = \App\Models\User::where('stripe_id', $object['customer'])->first();

        if ($user) {
            // Check if the subscription is active or trialing
            if (in_array($object['status'], ['active', 'trialing'])) {
                 // Assuming 'pro' is the only paid plan for now. 
                 // In a more complex setup, we'd check the price ID.
                $user->forceFill(['plan' => 'pro'])->save();
            } else {
                 // If status is incomplete, past_due, unpaid, etc., we might want to downgrade or handle differently.
                 // For MVP, let's keep it simple: if it's not active/trialing, it's free (or we wait for deletion).
                 // Actually, let's not downgrade immediately on past_due to allow for retries.
                 // We only downgrade on 'deleted' or if we explicitly want to handle 'canceled'.
            }
        }
    }

    protected function handleSubscriptionDeleted(array $payload)
    {
        $object = $payload['data']['object'];
        $user = \App\Models\User::where('stripe_id', $object['customer'])->first();

        if ($user) {
            $user->forceFill(['plan' => 'free'])->save();
        }
    }
}
