<?php

namespace App\Http\Middleware;

use Closure;

class ClickjackingHeaders
{
    public function handle($request, Closure $next)
    {
        $res = $next($request);

        // Legacy but widely supported
        $res->headers->set('X-Frame-Options', 'DENY');

        // Ensure CSP includes frame-ancestors 'none'
        $current = $res->headers->get('Content-Security-Policy'); // may be null
        if ($current) {
            if (stripos($current, 'frame-ancestors') === false) {
                $res->headers->set(
                    'Content-Security-Policy',
                    rtrim($current, " ;") . "; frame-ancestors 'none'"
                );
            }
        } else {
            $res->headers->set('Content-Security-Policy', "frame-ancestors 'none'");
        }

        return $res;
    }
}
