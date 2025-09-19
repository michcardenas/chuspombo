<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Auth\Events\PasswordReset;

class AuthController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
    
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->route('admin.dashboard');
        }
    
        return back()->withErrors([
            'email' => 'Las credenciales no son válidas.',
        ])->withInput();
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }

    /* =========================
     * RECUPERAR / RESETEAR PASSWORD
     * ========================= */

    // 1) Formulario: ¿Olvidaste tu contraseña?
    public function showForgotPasswordForm()
    {
        return view('auth.forgot-password'); // crea resources/views/auth/forgot-password.blade.php
    }

    // 2) Enviar enlace de reseteo al correo
    public function sendResetLinkEmail(Request $request)
    {
        $request->validate([
            'email' => ['required','email'],
        ]);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? back()->with('status', __($status)) // "We have emailed your password reset link."
            : back()->withErrors(['email' => __($status)]);
    }

    // 3) Mostrar formulario para definir nueva contraseña (viene con token)
    public function showResetForm(Request $request, $token = null)
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => $request->query('email'),
        ]);
    }

    // 4) Actualizar contraseña
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token'    => ['required'],
            'email'    => ['required','email'],
            'password' => ['required','confirmed','min:8'],
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => Hash::make($request->password),
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status)) // "Your password has been reset!"
            : back()->withErrors(['email' => [__($status)]])->withInput();
    }
}
