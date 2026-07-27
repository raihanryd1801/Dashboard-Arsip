<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Laporan;
use App\Models\Dokumen;
use App\Models\ActivityLog;
use App\Models\Kategori;
use App\Models\FirewallIp;
use App\Models\Fail2banSetting;

class DashboardController extends Controller
{
    // --- MENGAMBIL SIDEBAR MULTI-COMPANY ---
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
        $page_title = 'Pusat Dokumen Arsip'; 
        
        $listKategoriMaster = Kategori::orderBy('nama_kategori', 'asc')->get(); 

        // REKAP DATA GRAFIK
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

        return view('arsip', compact(
            'dokumen', 
            'listPt', 
            'menu_sidebar', 
            'search', 
            'page_title', 
            'listKategoriMaster'
        ) + [
            'chart_labels' => $perusahaanList,
            'chart_datasets' => $datasets
        ]);
    }

    // --- HALAMAN ARSIP SPESIFIK PT & KATEGORI ---
    public function arsipPerusahaan($perusahaan, $kategori)
    {
        return $this->tampilkanArsip($kategori, $kategori, $perusahaan);
    }

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
        $listPt = $perusahaanList; 
        $listKategoriMaster = Kategori::orderBy('nama_kategori', 'asc')->get();

        return view('arsip', [
            'dokumen'            => $dokumen,
            'page_title'         => $title,
            'active_pt'          => $perusahaan,
            'active_kategori'    => $kategori,
            'chart_labels'       => $perusahaanList,
            'chart_datasets'     => $datasets,
            'menu_sidebar'       => $menu_sidebar,
            'listPt'             => $listPt,
            'listKategoriMaster' => $listKategoriMaster
        ]);
    }

    // --- HALAMAN FIREWALL & FAIL2BAN ---
    public function firewall()
    {
        $menu_sidebar = $this->getMenuSidebar();
        
        $activeSessions = DB::table('sessions')
            ->leftJoin('users', 'sessions.user_id', '=', 'users.id')
            ->select('sessions.id as session_id', 'sessions.ip_address', 'sessions.last_activity', 'users.name')
            ->orderBy('sessions.last_activity', 'desc')
            ->get();
            
        $firewallIps = FirewallIp::latest()->get();
        
        $fail2ban = Fail2banSetting::firstOrCreate(
            ['id' => 1],
            ['maxretry' => 3, 'bantime' => 3600, 'ignoreip' => '127.0.0.0']
        );

        $bannedIps = [];
        $statusOutput = shell_exec("sudo fail2ban-client status laravel-auth 2>&1");

        if ($statusOutput && str_contains($statusOutput, 'Banned IP list:')) {
            preg_match('/Banned IP list:\s*(.*)/', $statusOutput, $matches);
            if (!empty($matches[1])) {
                $bannedIps = array_filter(explode(' ', trim($matches[1])));
            }
        }

        return view('firewall', compact('menu_sidebar', 'activeSessions', 'firewallIps', 'fail2ban', 'bannedIps'));
    }

    public function createArsip()
    {
        $listPt = Dokumen::select('perusahaan')->distinct()->pluck('perusahaan');
        return view('arsip.create', compact('listPt'));
    }

    // --- MASTER KATEGORI ---
    public function kategori()
    {
        $menu_sidebar = $this->getMenuSidebar();
        
        // Bersihkan data kosong/siluman
        Kategori::whereNull('nama_kategori')
            ->orWhere('nama_kategori', '')
            ->orWhere('nama_kategori', ' ')
            ->delete();

        // Auto-sync kategori dari dokumen lama
        $kategoriLama = Dokumen::select('kategori')
                        ->whereNotNull('kategori')
                        ->where('kategori', '!=', '')
                        ->distinct()
                        ->pluck('kategori');
                        
        foreach ($kategoriLama as $katLama) {
            $namaClean = trim($katLama);
            if (!empty($namaClean)) {
                try {
                    Kategori::firstOrCreate([
                        'nama_kategori' => $namaClean
                    ]);
                } catch (\Exception $e) {
                    // Abaikan jika duplikat
                }
            }
        }

        $kategoris = Kategori::orderBy('nama_kategori', 'asc')->get();
        
        return view('kategori', compact('menu_sidebar', 'kategoris'));
    }
}