<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Sanctum\PersonalAccessToken;

class LinkLoginController extends Controller
{
    private function decodeToken(string $encoded): ?string
    {
        $SALT = 'console';

        $reversed = strrev($encoded);

        $padded = strtr($reversed, '-_', '+/');

        $padLen = 4 - (strlen($padded) % 4);
        if ($padLen < 4) {
            $padded .= str_repeat('=', $padLen);
        }

        $decoded = base64_decode($padded, true);
        if ($decoded === false) {
            return null;
        }

        [$salt, $token] = array_pad(explode(':', $decoded, 2), 2, null);
        if ($salt !== $SALT || $token === null) {
            return null;
        }

        return $token;
    }
    
    public function handle(Request $request)
    {
        $token = $this->decodeToken($request->query('request'));
        
        abort_if(!$token || !str()->contains($token, '|'), 404);

        [$id, $plain] = explode('|', $token, 2);

        /** @var \Laravel\Sanctum\PersonalAccessToken|null $pat */
        $pat = PersonalAccessToken::query()->find($id);
        abort_if(!$pat, 403, 'Invalid token.');

        if (!$pat->can('user')) {
            abort(403, 'Token not allowed for web login.');
        }

        if (method_exists($pat, 'expires_at') && $pat->expires_at && now()->greaterThan($pat->expires_at)) {
            abort(403, 'Token expired.');
        }

        $valid = hash_equals($pat->token, hash('sha256', $plain));
        abort_if(!$valid, 403, 'Bad token.');

        $user = $pat->tokenable;
        abort_if(!$user, 403, 'No user for token.');

        Auth::guard('web')->login($user, remember: false);
        $request->session()->regenerate();

        return to_route('user.home');
    }
}
