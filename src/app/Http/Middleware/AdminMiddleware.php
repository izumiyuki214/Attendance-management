<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * 管理者判定ミドルウェア
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (!$request->user() || !$request->user()->admin_status) {
            return redirect('/admin/login');
        }

        return $next($request);
    }
}