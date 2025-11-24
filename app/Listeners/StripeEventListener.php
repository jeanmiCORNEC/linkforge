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

        if ($type === 'invoice.payment_succeeded') {
            $this->handlePaymentSucceeded($payload);
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

    protected function handlePaymentSucceeded(array $payload)
    {
        $object = $payload['data']['object'];
        
        // On cherche le client qui vient de payer
        $user = \App\Models\User::where('stripe_id', $object['customer'])->first();

        if ($user && $user->referrer_id && ! $user->referral_rewarded_at) {
            // On trouve le parrain
            $referrer = \App\Models\User::find($user->referrer_id);

            if ($referrer) {
                // 1. Si le parrain est déjà Pro (abonnement actif)
                if ($referrer->subscribed('pro')) {
                    // On lui offre un crédit Stripe de 9.90€ (1 mois)
                    $referrer->applyBalance(990, 'EUR', [
                        'description' => 'Récompense Parrainage (Filleul: ' . $user->username . ')',
                    ]);
                } else {
                    // 2. Si le parrain est Gratuit
                    // On lui offre 30 jours de Pro via trial_ends_at
                    $currentTrialEnd = $referrer->trial_ends_at ?? now();
                    // Si le trial est déjà passé, on repart de now()
                    if ($currentTrialEnd->isPast()) {
                        $currentTrialEnd = now();
                    }
                    
                    $referrer->forceFill([
                        'trial_ends_at' => $currentTrialEnd->addDays(30)
                    ])->save();
                }

                // On marque le filleul comme "récompensé" pour ne pas payer 2 fois
                $user->forceFill(['referral_rewarded_at' => now()])->save();
                
                Log::info("Referral Reward applied for referrer {$referrer->id} from user {$user->id}");
            }
        }
    }
}
