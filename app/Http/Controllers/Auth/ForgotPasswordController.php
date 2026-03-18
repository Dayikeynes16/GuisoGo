<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;

class ForgotPasswordController extends Controller
{
    public function create(): Response
    {
        return Inertia::render('Auth/ForgotPassword');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255'],
        ], [
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'Ingresa un correo valido.',
        ]);

        // Send reset link. We always return the same message regardless of whether
        // the email exists, to prevent user enumeration attacks.
        Password::broker('users')->sendResetLink(
            $request->only('email'),
        );

        return back()->with('success', 'Si el correo esta registrado, te enviaremos instrucciones para restablecer tu contrasena.');
    }
}
