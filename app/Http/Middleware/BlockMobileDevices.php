<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BlockMobileDevices
{
    public function handle(Request $request, Closure $next)
    {
        if (! \App\Models\AppSetting::blockMobileDevicesEnabled()) {
            return $next($request);
        }

        if ($this->isMobile($request)) {
            return response()->view('mobile-warning');
        }

        return $next($request);
    }

    protected function isMobile(Request $request): bool
    {
        $agent = $request->header('User-Agent', '');
        return (bool) preg_match('/(android|webos|iphone|ipad|ipod|blackberry|iemobile|opera mini|mobile|tablet)/i', $agent);
    }
}
