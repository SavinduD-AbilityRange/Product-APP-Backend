<?php
namespace App\Helpers;

use Firebase\JWT\JWT;

class TokenHelper
{
    public static function createToken($user)
    {
        return self::createJWT($user);
    }
    public static function createJWT($user)
    {
        $payload = [
            'iss' => "product-app-backend",
            'sub' => $user->id,
            'email' => $user->email,
            'role' => $user->role ?? null,
            'iat' => time(),
            'exp' => time() + 3600,
        ];
        $key = env('JWT_SECRET', 'your-secret-key');
        return JWT::encode($payload, $key, 'HS256');
    }

    public static function createApiToken()
    {
        return bin2hex(random_bytes(32));
    }

    public static function createNormalToken()
    {
        return bin2hex(random_bytes(16));
    }
}
