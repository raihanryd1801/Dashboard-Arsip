<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB; // Harus di-import agar bisa pakai DB::table
use App\Models\Laporan;
use App\Models\Dokumen;
use App\Models\ActivityLog;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil input pencarian
        $search = $request->input('search');

        // 2. Query log dengan fitur pencarian & pagination
        $logs = ActivityLog::query()
            ->when($search, function ($query, $search) {
                return $query->where('judul', 'like', "%{$search}%")
                             ->orWhere('kategori', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10);

        // 3. Hitung user online dari tabel sessions
        $user_online = DB::table('sessions')->count();

        // 4. Kirim semua data ke view dashboard
        return view('dashboard', [
            'label_bulan' => Laporan::pluck('bulan'),
            'data_jumlah' => Laporan::pluck('jumlah'),
            'logs'        => $logs,
            'search'      => $search,
            'user_online' => $user_online // Sudah masuk di sini
        ]);
    }

    public function arsip()
    {
        $dokumen = Dokumen::all();
        $laporan = Laporan::all();
        
        return view('arsip', [
            'dokumen'     => $dokumen,
            'label_bulan' => $laporan->pluck('bulan'),
            'data_jumlah' => $laporan->pluck('jumlah')
        ]);
    }
}