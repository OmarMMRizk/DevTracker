<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureApiEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        
        if (!$request->user() || !$request->user()->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'يجب تأكيد البريد الإلكتروني أولاً.',
                'verified' => false
            ], 403);
        }

        return $next($request);
        
    }
}
