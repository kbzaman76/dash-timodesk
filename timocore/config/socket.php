<?php

return [
    'token_session_minutes' => '30',
    'url' => env('SOCKET_SERVER_URL', 'https://wss.stakegame.net/timo-api'),
    'secret' => env('SOCKET_SERVER_SECRET', 'super-secret'),
];
