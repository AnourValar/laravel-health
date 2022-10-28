<?php

namespace AnourValar\LaravelHealth\Http\Controllers;

use App\Http\Controllers\Controller;

class ReverseProxySecurityController extends Controller
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
        ]);
    }
}
