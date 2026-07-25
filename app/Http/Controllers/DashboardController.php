<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Laporan;
use App\Models\Dokumen;
use App\Models\ActivityLog;

class DashboardController extends Controller
{
    // --- FUNGSI BARU: Mengambil Sidebar Multi-Company ---
    public function getMenuSidebar()
    {
        return Dokumen::select('perusahaan', 'kategori')
            ->distinct()
            ->orderBy('perusahaan')
            ->orderBy('kategori')
            ->get()
            ->groupBy('perusahaan');
    }

    // --- HALAMAN DASHBOARD UTAMA ---
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

        $statPerusahaan = Dokumen::select('perusahaan', DB::raw('count(*) as total'))
            ->groupBy('perusahaan')
            ->get();
        

        $ipData = $activeSessions->groupBy('ip_address')->map->count();

        // PANGGIL MENU SIDEBAR BARU
        $menu_sidebar = $this->getMenuSidebar();

        return view('dashboard', [
            'label_bulan'    => Laporan::pluck('bulan'),
            'data_upload'    => Laporan::pluck('upload_count'),
            'data_delete'    => Laporan::pluck('delete_count'),
            'logs'           => $logs,
            'search'         => $search,
            'user_online'    => $user_online,
            'pie_labels'     => $statPerusahaan->pluck('perusahaan'),
            'pie_data'       => $statPerusahaan->pluck('total'),
            'activeSessions' => $activeSessions,
            'ip_labels'      => $ipData->keys(),
            'ip_data'        => $ipData->values(),
            'menu_sidebar'   => $menu_sidebar 
        ]);
    }

    public function converter()
    {
        $menu_sidebar = $this->getMenuSidebar();
        return view('converter', ['menu_sidebar' => $menu_sidebar]);
    }

    // --- HALAMAN PUSAT DOKUMEN ---
    // --- HALAMAN PUSAT DOKUMEN ---
    public function arsip(Request $request)
    {
        $search = $request->input('search');
        
        $dokumen = Dokumen::when($search, function($q, $search) {
                return $q->where('judul', 'like', "%{$search}%")
                         ->orWhere('perusahaan', 'like', "%{$search}%");
            })
            ->latest()
            ->get();

        $listPt = Dokumen::select('perusahaan')->distinct()->pluck('perusahaan');
        $menu_sidebar = $this->getMenuSidebar();
        $page_title = 'Pusat Dokumen Arsip'; // <--- Tambahkan variabel ini agar view tidak error

        return view('arsip', compact('dokumen', 'listPt', 'menu_sidebar', 'search', 'page_title'));
    }

    // --- HALAMAN ARSIP SPESIFIK PT & KATEGORI ---
    public function arsipPerusahaan($perusahaan, $kategori)
    {
        return $this->tampilkanArsip($kategori, $kategori, $perusahaan);
    }

    // --- FUNGSI BANTUAN TAMPILKAN ARSIP ---
    private function tampilkanArsip($title, $kategori, $perusahaan)
    {
        if ($kategori != '' && $perusahaan != '') {
            $dokumen = Dokumen::where('perusahaan', $perusahaan)
                              ->where('kategori', $kategori)
                              ->latest()
                              ->get();
        } else {
            $dokumen = Dokumen::latest()->get();
        }

        $perusahaanList = Dokumen::select('perusahaan')->distinct()->pluck('perusahaan');
        $kategoriList = Dokumen::select('kategori')->distinct()->pluck('kategori');

        $datasets = [];
        $warnaKategori = ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899', '#0ea5e9'];
        $indexWarna = 0;

        foreach ($kategoriList as $kat) {
            $dataPerPt = [];
            foreach ($perusahaanList as $pt) {
                $jumlah = Dokumen::where('perusahaan', $pt)->where('kategori', $kat)->count();
                $dataPerPt[] = $jumlah;
            }

            $datasets[] = [
                'label' => $kat,
                'data' => $dataPerPt,
                'backgroundColor' => $warnaKategori[$indexWarna % count($warnaKategori)],
                'borderRadius' => 4
            ];
            $indexWarna++;
        }

        $menu_sidebar = $this->getMenuSidebar();
        $listPt = $perusahaanList; // Ditambahkan agar arsip.blade.php tidak error $listPt

        return view('arsip', [
            'dokumen'       => $dokumen,
            'page_title'    => $title,
            'active_pt'     => $perusahaan,
            'chart_labels'  => $perusahaanList,
            'chart_datasets'=> $datasets,
            'menu_sidebar'  => $menu_sidebar,
            'listPt'        => $listPt
        ]);
    }

    public function firewall()
    {
        $menu_sidebar = $this->getMenuSidebar();
        
        $activeSessions = DB::table('sessions')
            ->leftJoin('users', 'sessions.user_id', '=', 'users.id')
            ->select('sessions.id as session_id', 'sessions.ip_address', 'sessions.last_activity', 'users.name')
            ->orderBy('sessions.last_activity', 'desc')
            ->get();
            
        $firewallIps = \App\Models\FirewallIp::latest()->get();

        return view('firewall', compact('menu_sidebar', 'activeSessions', 'firewallIps'));
    }

    public function createArsip()
    {
        // Menggunakan model Dokumen (bukan Arsip) agar sinkron dengan database
        $listPt = Dokumen::select('perusahaan')->distinct()->pluck('perusahaan');
        
        return view('arsip.create', compact('listPt'));
    }
}