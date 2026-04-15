<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    /**
     * Show the login form
     */
    public function showLoginForm()
    {
        return view('auth.login');
    }

    /**
     * Process login
     */
    public function login(Request $request)
    {
        $loginType = $request->input('login_type', 'default');
        
        $rules = [
            'username' => 'required|string',
            'password' => 'required|string',
        ];

        if ($loginType === 'guru') {
            $rules['email'] = 'required|email';
        }

        // Validasi input
        $credentials = $request->validate($rules);

        // Attempt login (hanya pakai username & password untuk koneksi ke tabel users)
        $authCredentials = [
            'username' => $credentials['username'],
            'password' => $credentials['password'],
        ];

        if (Auth::attempt($authCredentials)) {
            $user = Auth::user();
            $roleName = $user->role->nama_role;

            // Validasi spesifik Guru
            if ($loginType === 'guru') {
                if ($roleName !== 'guru') {
                    Auth::logout();
                    return back()->withErrors(['username' => 'Akun ini bukan akun Guru. Silakan login sebagai Siswa/Admin.'])->withInput($request->only('username', 'login_type'));
                }

                // Cek kesesuaian email di tabel guru
                if (!$user->guru || $user->guru->email !== $credentials['email']) {
                    Auth::logout();
                    return back()->withErrors(['email' => 'Email yang Anda masukkan tidak sesuai dengan data terdaftar.'])->withInput($request->only('username', 'email', 'login_type'));
                }
            } else {
                // Mencegah guru login via form default (opsional tapi disarankan sesuai permintaan)
                if ($roleName === 'guru') {
                    Auth::logout();
                    return back()->withErrors(['username' => 'Guru harus login melalui form "Masuk sebagai Guru".'])->withInput();
                }
            }

            $request->session()->regenerate();

            // Redirect berdasarkan role
            switch ($roleName) {
                case 'admin':
                    return redirect()->intended('/dashboard');
                case 'guru':
                    return redirect()->intended('/guru/dashboard');
                case 'siswa':
                    return redirect()->intended('/siswa/dashboard');
                default:
                    return redirect('/');
            }
        }

        // Login gagal
        return back()->withErrors([
            'username' => 'Username atau password salah.',
        ])->withInput($request->only('username'));
    }

    /**
     * Process logout
     */
    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
