<?php
namespace App\Helpers;

use Firebase\JWT\JWT;

// 1st September 2025 - added the token - Ashini 19:36
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
            'role' => $user->role,
            'iat' => time(),
            'exp' => time() + 3600,
        ];
        // 2nd September 2025 Generate 256-bit JWT secret key - Ashini 
        $key = env('JWT_SECRET', bin2hex(random_bytes(32)));
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

    public static function generate256BitJWTSecret()
    {
        return bin2hex(random_bytes(32)); 
    }
}
