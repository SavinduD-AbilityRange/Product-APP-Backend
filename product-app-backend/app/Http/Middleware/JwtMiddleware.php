<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use App\Models\Customer;

class JwtMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        try {
            $authHeader = $request->header('Authorization');
            
            if (!$authHeader) {
                return response()->json(['error' => 'Authorization header missing'], 401);
            }

            if (!str_starts_with($authHeader, 'Bearer ')) {
                return response()->json(['error' => 'Invalid authorization format. Use: Bearer {token}'], 401);
            }

            $token = substr($authHeader, 7); // Remove "Bearer " prefix
            
            if (!$token) {
                return response()->json(['error' => 'Token missing'], 401);
            }

            $key = env('JWT_SECRET', 'your-secret-key');
            $decoded = JWT::decode($token, new Key($key, 'HS256'));

            $user = Customer::find($decoded->sub);
            
            if (!$user) {
                return response()->json(['error' => 'User not found'], 401);
            }

            $request->merge(['user' => $user]);
            $request->setUserResolver(function () use ($user) {
                return $user;
            });

            return $next($request);

        } catch (\Firebase\JWT\ExpiredException $e) {
            return response()->json(['error' => 'Token has expired'], 401);
        } catch (\Firebase\JWT\InvalidArgumentException $e) {
            return response()->json(['error' => 'Invalid token format'], 401);
        } catch (\Firebase\JWT\DomainException $e) {
            return response()->json(['error' => 'Token domain error'], 401);
        } catch (\Firebase\JWT\SignatureInvalidException $e) {
            return response()->json(['error' => 'Invalid token signature'], 401);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Authentication failed: ' . $e->getMessage()], 401);
        }
    }
}
