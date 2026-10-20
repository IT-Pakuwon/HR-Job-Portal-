<?php

// VAPID identity for Web Push (My Page app notifications). Keys are generated once with
// Minishlink\WebPush\VAPID::createVapidKeys() — changing them invalidates every existing subscription.
return [
    'public_key'  => env('VAPID_PUBLIC_KEY'),
    'private_key' => env('VAPID_PRIVATE_KEY'),
    'subject'     => env('VAPID_SUBJECT', 'mailto:admin@example.com'),
];
