<?php

namespace AnourValar\LaravelHealth\Http\Controllers;

class HealthPingController
{
    /**
     * @see \AnourValar\LaravelHealth\ReverseProxySecurityCheck
     *
     * @param \Illuminate\Http\Request $request
     * @return string
     */
    public function __invoke(\Illuminate\Http\Request $request)
    {
        return encrypt([
            'ip' => $request->ip(),
            'host' => parse_url(url(''), PHP_URL_HOST),
            'remote_addr' => $request->server('REMOTE_ADDR'),
            'port' => $request->getPort(),
            'is_secure' => $request->isSecure(),
        ]);
    }
}
