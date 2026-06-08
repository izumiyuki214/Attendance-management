<?php

namespace App\Http\Controllers;

use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class AuthController extends Controller
{
    // ========================================
    // 会員登録
    // ========================================

    /**
     * 会員登録画面を表示する
     */
    public function showRegister(): View
    {
        return view('auth.register');
    }

    /**
     * 会員登録処理
     */
    public function register(RegisterRequest $request): RedirectResponse
    {
        $user = User::create([
            'name'         => $request->name,
            'email'        => $request->email,
            'password'     => bcrypt($request->password),
            'admin_status' => false,
        ]);

        $user->sendEmailVerificationNotification();

        Auth::login($user);

        return redirect()->route('verification.notice');
    }

    // ========================================
    // ログイン（一般）
    // ========================================

    /**
     * ログイン画面を表示する
     */
    public function showLogin(): View
    {
        return view('auth.login');
    }

    /**
     * ログイン処理
     */
    public function login(LoginRequest $request): RedirectResponse
    {
        if (!Auth::attempt($request->only('email', 'password'), $request->boolean('remember'))) {
            return back()->withErrors([
                'email' => 'ログイン情報が登録されていません',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        return redirect()->intended(route('attendance.index'));
    }

    // ========================================
    // ログアウト（一般）
    // ========================================

    /**
     * ログアウト処理
     */
    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}