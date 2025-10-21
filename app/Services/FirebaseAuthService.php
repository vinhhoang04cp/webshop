<?php

namespace App\Services;

use App\Models\User;
use Exception;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Kreait\Firebase\Auth as FirebaseAuth;
use Kreait\Firebase\Exception\Auth\FailedToVerifyToken;
use Kreait\Firebase\Factory;

class FirebaseAuthService
{
    private FirebaseAuth $auth;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(config('firebase.credentials'));

        $this->auth = $factory->createAuth();
    }

    /**
     * Verify Firebase ID token and return user data
     */
    public function verifyIdToken(string $idToken): array
    {
        try {
            $verifiedToken = $this->auth->verifyIdToken($idToken);
            $claims = $verifiedToken->claims();

            return [
                'uid' => $claims->get('sub'),
                'email' => $claims->get('email'),
                'name' => $claims->get('name'),
                'picture' => $claims->get('picture'),
                'email_verified' => $claims->get('email_verified', false),
                'provider' => $this->detectProvider($claims->get('firebase', [])),
            ];
        } catch (FailedToVerifyToken $e) {
            throw new Exception('Invalid Firebase token: '.$e->getMessage());
        }
    }

    /**
     * Create or find user from Firebase token data
     */
    public function createOrFindUser(array $firebaseUserData): User
    {
        // Tìm user theo Firebase UID trước
        $user = User::where('firebase_uid', $firebaseUserData['uid'])->first();

        if ($user) {
            return $user;
        }

        // Nếu không tìm thấy, tìm theo email
        $user = User::where('email', $firebaseUserData['email'])->first();

        if ($user) {
            // Cập nhật user hiện tại với Firebase UID
            $user->update([
                'firebase_uid' => $firebaseUserData['uid'],
                'provider' => $firebaseUserData['provider'],
                'avatar' => $firebaseUserData['picture'] ?? null,
            ]);

            return $user;
        }

        // Tạo user mới
        return User::create([
            'name' => $firebaseUserData['name'],
            'email' => $firebaseUserData['email'],
            'firebase_uid' => $firebaseUserData['uid'],
            'provider' => $firebaseUserData['provider'],
            'avatar' => $firebaseUserData['picture'] ?? null,
            'email_verified_at' => $firebaseUserData['email_verified'] ? now() : null,
            'password' => Hash::make(Str::random(32)), // Random password vì user login qua Firebase
        ]);
    }

    /**
     * Detect login provider from Firebase claims
     */
    private function detectProvider(array $firebaseData): string
    {
        $identities = $firebaseData['identities'] ?? [];

        if (isset($identities['google.com'])) {
            return 'google';
        } elseif (isset($identities['facebook.com'])) {
            return 'facebook';
        } elseif (isset($identities['twitter.com'])) {
            return 'twitter';
        }

        return 'email'; // Default fallback
    }

    /**
     * Get user from Firebase UID
     */
    public function getUserByFirebaseUid(string $uid): ?User
    {
        return User::where('firebase_uid', $uid)->first();
    }

    /**
     * Create custom token for user
     */
    public function createCustomToken(string $uid, array $claims = []): string
    {
        return $this->auth->createCustomToken($uid, $claims);
    }

    /**
     * Disable Firebase user
     */
    public function disableUser(string $uid): void
    {
        $this->auth->disableUser($uid);
    }

    /**
     * Enable Firebase user
     */
    public function enableUser(string $uid): void
    {
        $this->auth->enableUser($uid);
    }
}
