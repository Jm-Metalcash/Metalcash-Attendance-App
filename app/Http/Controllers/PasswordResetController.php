<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Models\User;
use App\Notifications\ResetPasswordNotification;

class PasswordResetController extends Controller
{
    public function sendResetLink(Request $request)
    {
        // Valider l'e-mail
        $request->validate(['email' => 'required|email']);

        // Chercher l'utilisateur par son e-mail
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json(['error' => 'Aucun utilisateur trouvé avec cet e-mail.'], 404);
        }

        // Générer un jeton de réinitialisation de mot de passe
        $token = Password::createToken($user);

        // Envoyer la notification personnalisée avec le lien de réinitialisation
        $user->notify(new ResetPasswordNotification($token));

        return response()->json(['success' => 'Lien de réinitialisation envoyé avec succès.']);
    }
}
