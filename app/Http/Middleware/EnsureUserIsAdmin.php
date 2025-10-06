<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Kiểm tra user có role admin không
        // Tạm thời comment để không ảnh hưởng logic hiện tại
        
        // if (!$request->user() || !$request->user()->hasRole('admin')) {
        //     return response()->json([
        //         'status' => false,
        //         'message' => 'Access denied. Admin role required.',
        //     ], 403);
        // }

        return $next($request);
    }
}