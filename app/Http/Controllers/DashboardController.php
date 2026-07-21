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
        $search = $request->input('search');

        $logs = ActivityLog::query()
            ->when($search, function ($query, $search) {
                return $query->where('judul', 'like', "%{$search}%")
                             ->orWhere('kategori', 'like', "%{$search}%");
            })
            ->latest()
            ->paginate(10);

        $activeSessions = DB::table('sessions')
            ->leftJoin('users', 'sessions.user_id', '=', 'users.id')
            ->select('sessions.ip_address', 'sessions.last_activity', 'users.name', 'users.email')
            ->get();

        $user_online = $activeSessions->count();

        $kategoriData = Dokumen::select('kategori', DB::raw('count(*) as total'))
            ->groupBy('kategori')
            ->get();

        $ipData = $activeSessions->groupBy('ip_address')->map->count();

        // Ambil daftar kategori unik untuk sidebar dinamis
        $menu_kategori = Dokumen::select('kategori')->distinct()->pluck('kategori');

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
            'menu_kategori'  => $menu_kategori
        ]);
    }

    public function arsip()
    {
        return $this->tampilkanArsip('Pusat Dokumen', '');
    }

    // Fungsi penangkap rute dinamis kategori dokumen di sidebar
    public function kategori($nama_kategori)
    {
        return $this->tampilkanArsip($nama_kategori, $nama_kategori);
    }

    private function tampilkanArsip($title, $kategori)
    {
        if ($kategori != '') {
            $dokumen = Dokumen::where('kategori', 'LIKE', "%{$kategori}%")->latest()->get();
        } else {
            $dokumen = Dokumen::latest()->get();
        }

        $kategoriData = Dokumen::select('kategori', DB::raw('count(*) as total'))
            ->groupBy('kategori')
            ->get();

        // Ambil daftar kategori unik untuk sidebar dinamis
        $menu_kategori = Dokumen::select('kategori')->distinct()->pluck('kategori');

        return view('arsip', [
            'dokumen'       => $dokumen,
            'page_title'    => $title,
            'chart_labels'  => $kategoriData->pluck('kategori'),
            'chart_data'    => $kategoriData->pluck('total'),
            'menu_kategori' => $menu_kategori
        ]);
    }
}