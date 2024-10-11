<?php

namespace App\Http\Controllers;

use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');

        if (!$token = auth()->guard('partner')->attempt($credentials)) {
            return response()->json(['error' => 'Credenciais inválidas'], 401);
        }

        // Log para verificar o usuário autenticado
        $user = auth()->guard('partner')->user();
        \Log::info('User authenticated:', ['id' => $user->id, 'email' => $user->email]);

        return $this->respondWithToken($token);
    }

    protected function respondWithToken($token)
    {
        return response()->json([
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth()->guard('partner')->factory()->getTTL() * 60
        ]);
    }

    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Logout realizado com sucesso']);
    }

    public function me()
    {
        // return response()->json(auth()->user());

         // Obter o usuário autenticado
         $user = Auth::guard('partner')->user();
         if($user){
            $user->makeHidden([
                'password',
                'updated_at',
                'tokenesquecisenha',
                'tokenprimeiroacesso',
                'ultimoip',
                'provider',
                'provider_id',
                'primeiroacesso'
            ]);

            return response($user, 200);
         } else {
            return response()->json(['error' => 'Usuário não autenticado'], 401);
        }
    }

    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    public function handleFacebookCallback()
    {
        // Obter o código de autorização retornado pelo Facebook
        $code = $request->query('code');

        // Trocar o código de autorização por um token de acesso
        $user = Socialite::driver('facebook')->stateless()->userFromCode($code);

        // Aqui $user contém informações do usuário e o token de acesso

        return view('facebook.callback');
    }

    public function deleteUserData()
    {
        $token = $user->token;
        $response = Http::delete("https://graph.facebook.com/me?access_token={$token}");

        // Verifique a resposta para confirmar a exclusão dos dados

        return $response->json();
    }
}