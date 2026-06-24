<?php

namespace HoheiselIT\Lexoffice\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyLexofficeSignature
{
    public function handle(Request $request, Closure $next): Response
    {
        $secret = config('lexoffice.webhook.secret');

        // Skip verification if no secret configured (dev/testing).
        if (empty($secret)) {
            return $next($request);
        }

        $signature = $request->header('X-Lxo-Signature');

        if (! $signature) {
            abort(401, 'Missing webhook signature.');
        }

        $expected = hash_hmac('sha256', $request->getContent(), $secret);

        if (! hash_equals($expected, $signature)) {
            abort(403, 'Invalid webhook signature.');
        }

        return $next($request);
    }
}
