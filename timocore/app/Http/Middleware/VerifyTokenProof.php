<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use Illuminate\Support\Facades\Log;

class VerifyTokenProof
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $proofHeader = $request->header('X-Token-Proof');
        $authHeader = $request->header('Authorization');


        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return unauthorized();
        }

        $token = substr($authHeader, 7); // raw token string

        if (!$proofHeader) {
            return unauthorized();
        }

        $secret = (string) config('services.token_proof.secret');
        $maxDrift = (int) config('services.token_proof.drift', 180);
        $now = time();

        // Expect base64url(JSON) — { v, ts, nonce, token_hash, device_id?, sig }
        $json = static::b64urlDecode($proofHeader);
        if ($json === null) {
            return unauthorized();
        }

        $payload = json_decode($json, true);
        if (!is_array($payload)) {
            return unauthorized();
        }

        $version = $payload['v'] ?? 'v1';
        $ts = (int) ($payload['ts'] ?? 0);
        $nonce = (string) ($payload['nonce'] ?? '');
        $tokenHash = (string) ($payload['token_hash'] ?? '');
        $deviceId = (string) ($payload['device_id'] ?? '');
        $sig = (string) ($payload['sig'] ?? '');

        if ($version !== 'v1' || !$ts || !$nonce || !$tokenHash || !$sig) {
            return unauthorized();
        }

        // freshness check
        if (abs($now - $ts) > $maxDrift) {
            return unauthorized();
        }

        // recompute token_hash from raw bearer (client should send sha256(token))
        $serverTokenHash = hash('sha256', $token);

        if (!hash_equals($serverTokenHash, $tokenHash)) {
            return unauthorized();
        }

        // build signing string (bind device_id if provided)
        $signingParts = [$version, $ts, $nonce, $tokenHash];
        if ($deviceId !== '') {
            $signingParts[] = $deviceId;
        }
        $signingString = implode('|', $signingParts);

        $expectedSig = hash_hmac('sha256', $signingString, $secret);

        if (!hash_equals($expectedSig, $sig)) {
            return unauthorized();
        }

        // all good — pass through
        return $next($request);
    }

    private static function b64urlDecode(string $data): ?string
    {
        // tolerate plain base64 too
        $data = strtr($data, '-_', '+/');
        $pad = strlen($data) % 4;
        if ($pad)
            $data .= str_repeat('=', 4 - $pad);

        $decoded = base64_decode($data, true);
        return $decoded === false ? null : $decoded;
    }
}
