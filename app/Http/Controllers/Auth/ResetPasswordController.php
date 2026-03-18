<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password as PasswordRule;
use Inertia\Inertia;
use Inertia\Response;

class ResetPasswordController extends Controller
{
    public function create(Request $request, string $token): Response
    {
        return Inertia::render('Auth/ResetPassword', [
            'token' => $token,
            'email' => $request->query('email', ''),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email', 'max:255'],
            'password' => ['required', 'confirmed', PasswordRule::defaults()],
        ], [
            'email.required' => 'El correo es obligatorio.',
            'password.required' => 'La contrasena es obligatoria.',
            'password.confirmed' => 'Las contrasenas no coinciden.',
            'password.min' => 'La contrasena debe tener al menos 8 caracteres.',
        ]);

        $status = Password::broker('users')->reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, string $password): void {
                $user->forceFill([
                    'password' => Hash::make($password),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            },
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('success', 'Tu contrasena ha sido restablecida. Inicia sesion con tu nueva contrasena.');
        }

        $messages = [
            Password::INVALID_TOKEN => 'El enlace de restablecimiento es invalido o ha expirado. Solicita uno nuevo.',
            Password::INVALID_USER => 'No encontramos una cuenta con ese correo.',
            Password::RESET_THROTTLED => 'Has intentado demasiadas veces. Espera unos minutos.',
        ];

        return back()->withErrors([
            'email' => $messages[$status] ?? 'No se pudo restablecer la contrasena.',
        ]);
    }
}
