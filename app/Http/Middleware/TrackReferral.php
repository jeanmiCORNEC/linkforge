<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Cookie;

class TrackReferral
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($request->has('ref')) {
            $referralCode = $request->query('ref');
            
            // On stocke le code dans un cookie pour 30 jours (43200 minutes)
            // On utilise queue() pour l'attacher à la réponse automatiquement
            Cookie::queue('linkforge_ref', $referralCode, 43200);
        }

        return $next($request);
    }
}
