<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Models\Laporan;
use App\Models\Dokumen;
use App\Models\ActivityLog;
use App\Models\FirewallIp;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Middleware\IpFirewall;

// --- RUTE PUBLIK (Bisa diakses tanpa login, untuk autentikasi) ---
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// --- RUTE TERLINDUNGI (Wajib Login + Wajib Lewat Firewall IP Whitelist) ---
Route::middleware(['auth', IpFirewall::class])->group(function () {
    
    // 1. Dashboard Utama
    Route::get('/', [DashboardController::class, 'index']);
    
    // 2. Rute Update Profil & Password
    Route::post('/update-profile', [AuthController::class, 'updateProfile']);

    // Tambahkan rute ini untuk menangani update profil
    Route::middleware(['auth'])->group(function () {
    Route::put('/profile/update', [AuthController::class, 'updateProfile']);
    // Atau jika form HTML abang menggunakan method POST biasa:
    // Route::post('/profile/update', [AuthController::class, 'updateProfile']);
});
    // 3. RUTE ARSIP MULTI-COMPANY
    Route::get('/arsip', [DashboardController::class, 'arsip']);
    Route::get('/arsip/{perusahaan}/{kategori}', [DashboardController::class, 'arsipPerusahaan']);
    
    // 4. Menu Converter Image to PDF
    Route::get('/converter', [DashboardController::class, 'converter']);
    Route::post('/process-convert', function (Request $request) {
        $request->validate([
            'file_convert' => 'required|file|mimes:doc,docx,xls,xlsx,jpg,jpeg,png|max:20480',
        ]);
        return back()->with('success', 'File berhasil disiapkan untuk dikonversi!');
    });

    // 5. Menu Firewall & Session Manager
    Route::get('/firewall', [DashboardController::class, 'firewall']);
    Route::delete('/firewall/kick/{id}', function ($id) {
        DB::table('sessions')->where('id', $id)->delete();
        return back()->with('success', 'Sesi berhasil di-drop/ditendang!');
    });
    Route::post('/firewall/allow', function (Request $request) {
        $request->validate(['ip_address' => 'required|ip', 'keterangan' => 'nullable']);
        FirewallIp::create($request->only('ip_address', 'keterangan'));
        return back()->with('success', 'IP berhasil ditambahkan ke Firewall Allow-List!');
    });
    Route::delete('/firewall/revoke/{id}', function ($id) {
        FirewallIp::findOrFail($id)->delete();
        return back()->with('success', 'IP berhasil dicabut aksesnya!');
    });
    
    // 6. Upload Dokumen (Standar Aman & Cepat)
    Route::post('/upload-dokumen', function (Request $request) {
        $request->validate([
            'perusahaan' => 'required',
            'kategori' => 'required',
            'judul' => 'required',
            'tanggal_dokumen' => 'required|date',
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png|max:20480',
        ]);
        
        $file = $request->file('file');
        $originalName = str_replace([' ', '[', ']'], '_', $file->getClientOriginalName());
        $filename = time() . '_' . $originalName;
        $file->move(public_path('dokumen'), $filename);
        
        Dokumen::create([
            'perusahaan' => $request->perusahaan,
            'kategori' => $request->kategori,
            'judul' => $request->judul,
            'tanggal_dokumen' => $request->tanggal_dokumen,
            'file_path' => 'dokumen/' . $filename
        ]);

        ActivityLog::create([
            'aksi' => 'UPLOAD', 
            'kategori' => $request->perusahaan . ' - ' . $request->kategori, 
            'judul' => $request->judul
        ]);

        $bulan_sekarang = Carbon::now()->format('M');
        $laporan = Laporan::firstOrCreate(['bulan' => $bulan_sekarang], ['upload_count' => 0, 'delete_count' => 0]);
        $laporan->increment('upload_count');
        
        return back()->with('success', 'Dokumen berhasil di-upload!');
    });

    // 7. Hapus Dokumen
    Route::delete('/dokumen/{id}', function ($id) {
        $dokumen = Dokumen::findOrFail($id);
        
        ActivityLog::create([
            'aksi' => 'DELETE', 
            'kategori' => $dokumen->perusahaan . ' - ' . $dokumen->kategori, 
            'judul' => $dokumen->judul
        ]);

        if (file_exists(public_path($dokumen->file_path))) {
            unlink(public_path($dokumen->file_path));
        }
        
        $dokumen->delete();

        $bulan_sekarang = Carbon::now()->format('M');
        $laporan = Laporan::firstOrCreate(['bulan' => $bulan_sekarang], ['upload_count' => 0, 'delete_count' => 0]);
        $laporan->increment('delete_count');
        
        return back()->with('success', 'Dokumen dihapus & dicatat!');
    });

    // 8. Rute Fix Error View / Download Dokumen
    Route::get('/dokumen/{filename}', function ($filename) {
        $path = public_path('dokumen/' . $filename);
        if (file_exists($path)) {
            return response()->file($path);
        }
        return abort(404, 'File tidak ditemukan di server.');
    })->where('filename', '.*');

});