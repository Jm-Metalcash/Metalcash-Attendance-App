<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash; 
use Inertia\Inertia;
use Illuminate\Support\Str;

class NewPasswordController extends Controller
{
    // Afficher le formulaire pour la réinitialisation de mot de passe
    public function create(Request $request, $token)
    {
        return Inertia::render('Auth/ResetPassword', [
            'email' => $request->query('email'),
            'token' => $token,
        ]);
    }

    // Traiter la réinitialisation du mot de passe
    public function store(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ]);
    
        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, $password) {
                $user->forceFill([
                    'password' => Hash::make($password),
                ])->save();
    
                $user->setRememberToken(Str::random(60));
            }
        );
    
        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))  // Rediriger vers la route 'login'
            : back()->withErrors(['email' => [__($status)]]);
    }
    
    

}
