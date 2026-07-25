<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    // Fungsi untuk menampilkan halaman form login
    public function showLogin()
    {
        return view('login');
    }

    public function authenticate(Request $request)
    {
        // 1. Validasi inputan harus diisi
        $credentials = $request->validate([
            'email' => ['required'],
            'password' => ['required'],
        ]);

        // 2. Cek apakah email dan password cocok di database
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/'); // Jika benar, masuk ke dashboard
        }

        // 3. JIKA SALAH: Kembalikan ke halaman login dengan membawa pesan 'error'
        return back()->with('error', 'Email atau Password yang Anda masukkan salah!');
    }

    // Fungsi untuk proses logout
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    }
    // --- FUNGSI UPDATE PROFIL ---
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        // Validasi inputan (Ditambah 'confirmed' agar sinkron dengan password_confirmation)
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6|confirmed' // <--- Tambah 'confirmed' di sini
        ]);

        // Update nama dan email
        $user->name = $request->name;
        $user->email = $request->email;

        // Jika kolom password diisi, maka update password
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return back()->with('success', 'Profil dan Password berhasil diperbarui!');
    }
}

