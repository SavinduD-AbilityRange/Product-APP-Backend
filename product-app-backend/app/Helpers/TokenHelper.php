<?php
namespace App\Helpers;

use Firebase\JWT\JWT;

class TokenHelper
{
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
        $key = env('JWT_SECRET', 'your-secret-key');
        return JWT::encode($payload, $key, 'HS256');
    }

    public static function createApiToken()
    {
        //[02/09/2025 |Asmitha T| 11.46] Generate an 8-character 
        return substr(str_shuffle('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789'), 0, 8);
    }

    public static function createNormalToken()
    {
        return bin2hex(random_bytes(16));
    }
}