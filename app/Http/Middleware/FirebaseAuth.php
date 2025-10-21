<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Kreait\Firebase\Auth as FirebaseAuthService;
use Kreait\Firebase\Factory;
use Symfony\Component\HttpFoundation\Response;

class FirebaseAuth
{
    private FirebaseAuthService $auth;

    public function __construct()
    {
        try {
            $factory = (new Factory)
                ->withServiceAccount(config('firebase.credentials'));

            $this->auth = $factory->createAuth();
        } catch (Exception $e) {
            // Log error hoặc handle appropriately
            \Log::error('Firebase initialization failed: '.$e->getMessage());
        }
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            return response()->json([
                'error' => 'Firebase token is required',
            ], 401);
        }

        try {
            $verifiedToken = $this->auth->verifyIdToken($token);
            $uid = $verifiedToken->claims()->get('sub');

            // Thêm user info vào request
            $request->merge(['firebase_uid' => $uid]);
            $request->merge(['firebase_token' => $verifiedToken]);

        } catch (Exception $e) {
            return response()->json([
                'error' => 'Invalid Firebase token',
                'message' => $e->getMessage(),
            ], 401);
        }

        return $next($request);
    }
}
