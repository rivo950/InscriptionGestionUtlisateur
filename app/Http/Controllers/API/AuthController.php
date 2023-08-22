<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use Illuminate\Support\Str;
use App\Mail\ActivationMail;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Exceptions\JWTException;

class AuthController extends Controller
{
        //enregistrer un nouvel utilisateur dans la base de données ,
    public function register(Request $request)
    {
        $user = new User();
        $user->api_token = Str::random(60);
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->save();

        // Envoyez un e-mail avec le token d'activation 
        Mail::to($user->email)->send(new ActivationMail($user, $user->activation_token));

        //renvoier une réponse JSON indiquant qu'une inscription a réussi ou echec.
        return response()->json([
            'message' => 'Inscription réussie',
            'user' => $user
        ]);
    }
        //Tâche 2: Créer une API pour activer le compte d'utilisateur en utilisant le token d'activation. L'API devrait permettre de :
    public function activateAccount($token)
    {
        $utilisateur = User::where('activation_token', $token)->first();

        if (!$utilisateur) {
            return response()->json(['message' => 'Token d\'activation invalide'], 404);
        }

        $utilisateur->update([
            'activation_token' => null,
            'active' => true,
        ]);

        return response()->json(['message' => 'Votre compte a été activé. Veuillez vous connecter']);
    }
    //Tâche 3: Mettre en place une API de connexion utilisateur
    public function login(Request $request)
        {
            $credentials = $request->only('email', 'password');

            try {
                if (!$token = JWTAuth::attempt($credentials)) {
                    return response()->json(['error' => 'Identification non valides'], 401);
                }
            } catch (JWTException $e) {
                return response()->json(['error' => 'Impossible de créer un token'], 500);
            }

            return response()->json(['token' => $token]);
        }
}
