<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * CheckTokenExpirationMiddleware - Kiểm tra token hết hạn
 *
 * Middleware này:
 * - Kiểm tra thời gian tồn tại của token
 * - Tự động xóa token hết hạn
 * - Giới hạn số lượng token cho mỗi user
 */
class CheckTokenExpirationMiddleware
{
    /**
     * Token expiration time in days
     */
    protected int $expirationDays = 30;

    /**
     * Maximum tokens per user
     */
    protected int $maxTokensPerUser = 5;

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (! $user) {
            return $next($request);
        }

        $token = $user->currentAccessToken();

        if (! $token) {
            return response()->json([
                'status' => false,
                'message' => 'Token không hợp lệ.',
            ], 401);
        }

        // Kiểm tra token đã hết hạn chưa
        if ($token->created_at->diffInDays(now()) > $this->expirationDays) {
            $token->delete();

            return response()->json([
                'status' => false,
                'message' => 'Token đã hết hạn. Vui lòng đăng nhập lại.',
                'error_code' => 'TOKEN_EXPIRED',
            ], 401);
        }

        // Cập nhật last_used_at
        $token->forceFill([
            'last_used_at' => now(),
        ])->save();

        // Giới hạn số lượng token
        $this->limitTokens($user);

        return $next($request);
    }

    /**
     * Limit the number of tokens per user
     */
    protected function limitTokens($user): void
    {
        $tokenCount = $user->tokens()->count();

        if ($tokenCount > $this->maxTokensPerUser) {
            // Xóa các token cũ nhất
            $user->tokens()
                ->orderBy('created_at', 'asc')
                ->limit($tokenCount - $this->maxTokensPerUser)
                ->delete();
        }
    }
}
