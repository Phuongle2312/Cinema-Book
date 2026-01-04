<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Kiểm tra user đã đăng nhập chưa
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Vui lòng đăng nhập'
            ], 401);
        }

        // Kiểm tra role có phải admin không
        if ($request->user()->role !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden - Bạn không có quyền truy cập'
            ], 403);
        }

        return $next($request);
    }
}
