<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Exceptions\TokenExpiredException;
use Tymon\JWTAuth\Exceptions\TokenInvalidException;
use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;

class AuthenticateJWT
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle($request, Closure $next)
    {
        try {
            $user = Auth::guard('partner')->check();

            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

        } catch (TokenExpiredException $e) {
            return response()->json(['error' => 'Token expired'], 401);

        } catch (TokenInvalidException $e) {
            return response()->json(['error' => 'Token invalid'], 401);

        } catch (Exception $e) {
            return response()->json(['error' => 'Authorization token not found'], 401);
        }

        return $next($request);
    }
}
