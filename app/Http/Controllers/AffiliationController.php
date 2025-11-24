<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;

class AffiliationController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        // On compte combien de filleuls ont payé (récompensés)
        $referralsCount = \App\Models\User::where('referrer_id', $user->id)
            ->whereNotNull('referral_rewarded_at')
            ->count();

        // On compte les clics sur le lien (si on voulait tracker ça, pour l'instant on compte juste les inscrits)
        // Pour l'instant on reste simple : nombre de filleuls convertis.

        return Inertia::render('Affiliation', [
            'referralCode' => $user->referral_code,
            'referralsCount' => $referralsCount,
            'referralLink' => $user->referral_code ? route('welcome', ['ref' => $user->referral_code]) : null,
        ]);
    }

    public function generate(Request $request)
    {
        $user = $request->user();

        if (! $user->referral_code) {
            // Génère un code unique : MIKE4921
            do {
                $code = strtoupper(substr($user->username, 0, 3) . Str::random(5));
            } while (\App\Models\User::where('referral_code', $code)->exists());

            $user->forceFill(['referral_code' => $code])->save();
        }

        return back()->with('success', 'Votre lien d\'affiliation a été généré !');
    }
}
