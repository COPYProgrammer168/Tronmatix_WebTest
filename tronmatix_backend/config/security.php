<?php

return [
    // ── Forgot-password brute-force / abuse protection ─────────────────────────
    'forgot_password' => [
        // Max failed submissions (unknown email) per IP before a temporary ban.
        'ip_max_attempts' => 10,

        // How long the IP lockout lasts, in minutes.
        'ip_lockout_minutes' => 60,

        // How long a successful submission blocks re-submission of the SAME
        // email, in minutes.
        'email_cooldown_minutes' => 60,
    ],
];
