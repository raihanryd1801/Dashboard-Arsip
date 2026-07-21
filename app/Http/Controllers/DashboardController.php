<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Laporan;
use App\Models\Dokumen;
use App\Models\ActivityLog;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil input pencarian riwayat dokumen
        $search = $request->input('search');

        // 2. Query log dengan fitur pencarian & pagination
        $logs = ActivityLog::query()
            ->when($search, function ($query, $search) {
                return $query->where('judul', 'like', "%{$search}%")
                             ->orWhere('kategori', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10);

        // 3. Ambil data sesi aktif langsung dari tabel 'sessions' bawaan Laravel
        $activeSessions = DB::table('sessions')
            ->leftJoin('users', 'sessions.user_id', '=', 'users.id')
            ->select('sessions.ip_address', 'sessions.last_activity', 'users.name', 'users.email')
            ->get();

        $user_online = $activeSessions->count();

        // 4. Data untuk Pie Chart Kategori Dokumen
        $kategoriData = Dokumen::select('kategori', DB::raw('count(*) as total'))
            ->groupBy('kategori')
            ->get();

        // 5. Data untuk Pie Chart berdasarkan IP Address dari tabel sessions
        $ipData = $activeSessions->groupBy('ip_address')->map->count();

        return view('dashboard', [
            'label_bulan'    => Laporan::pluck('bulan'),
            'data_jumlah'    => Laporan::pluck('jumlah'),
            'logs'           => $logs,
            'search'         => $search,
            'user_online'    => $user_online,
            'pie_labels'     => $kategoriData->pluck('kategori'),
            'pie_data'       => $kategoriData->pluck('total'),
            'activeSessions' => $activeSessions,
            'ip_labels'      => $ipData->keys(),
            'ip_data'        => $ipData->values(),
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