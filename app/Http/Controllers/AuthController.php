<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;      // <-- Wajib ditambahkan
use App\Models\ActivityLog;              // <-- Wajib ditambahkan

class AuthController extends Controller
{
    // Fungsi untuk menampilkan halaman form login
    public function showLogin()
    {
        return view('login');
    }

    public function authenticate(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();
            return redirect()->intended('/');
        }

        // --- AWAL KODE LOG GAGAL LOGIN ---
        $ipAddress = $request->ip();
        $emailAttempt = $request->email;

        // 1. Catat ke File laravel.log (KHUSUS UNTUK DIBACA FAIL2BAN)
        // Pola teks ini yang nanti dicari oleh regex Fail2ban
        Log::warning("[AUTH-FAILED] Failed login attempt from IP: {$ipAddress} for email: {$emailAttempt}");

        // 2. Catat ke Database (Untuk tampil di Dashboard NOC)
        ActivityLog::create([
            'aksi' => 'WARNING', 
            'kategori' => 'Security Alert', 
            'judul' => "Gagal login dari IP: {$ipAddress} (Email: {$emailAttempt})"
        ]);
        // --- AKHIR KODE LOG GAGAL LOGIN ---

        return back()->withErrors([
            'email' => 'Kredensial Email atau Password yang diberikan Salah.',
        ])->onlyInput('email');
    }

    // ... (fungsi logout & updateProfile biarkan saja) ...


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

