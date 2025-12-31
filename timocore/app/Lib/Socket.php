<?php

namespace App\Lib;

use Illuminate\Support\Facades\Http;
use App\Enums\SystemEventType;
use Illuminate\Support\Facades\Cache;

class Socket
{
    public static function onlineUsersCount()
    {
        return Cache::remember('socket_online_users_count', 5, function () {
            try {
                $response = Http::withHeaders([
                    'X-SOCKET-SECRET' => config('socket.secret'),
                ])->timeout(3)->get(config('socket.url') . '/online-users-count/' . auth()->user()->organization_id);

                $data = json_decode($response->body(), true);

                return is_int($data) ? $data : 0;
            } catch (\Throwable $e) {
                return 0;
            }
        });
    }

    public static function onlineUsers(): array
    {
        $response = Http::withHeaders([
            'X-SOCKET-SECRET' => config('socket.secret'),
        ])->get(config('socket.url') . '/online-users/' . auth()->user()->organization_id);

        return json_decode($response->body(), true) ?? [];
    }

    public static function emit(
        array|string $rooms,
        SystemEventType $type,
        array $payload = [],
        array $meta = []
    ): void {
        Http::withHeaders([
            'X-SOCKET-SECRET' => config('socket.secret'),
        ])->post(config('socket.url') . '/emit', [
                    'rooms' => (array) $rooms,
                    'event' => 'system:event',
                    'data' => [
                        'type' => $type->value,
                        'payload' => $payload,
                        'meta' => array_merge([
                            'timestamp' => now()->toIso8601String(),
                        ], $meta),
                    ],
                ]);
    }
}
