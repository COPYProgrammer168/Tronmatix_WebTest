<?php

// app/Http/Middleware/TrustProxies.php
// Render uses a load balancer — Laravel must trust it to detect HTTPS correctly.
// Without this, Laravel generates http:// URLs even though Render serves https://

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * Trust ALL proxies — required for Render, Railway, Heroku, etc.
     * These platforms sit behind a load balancer that forwards X-Forwarded-* headers.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies = '*';

    /**
     * Trust all standard forwarded headers.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
