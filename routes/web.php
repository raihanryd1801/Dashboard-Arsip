<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Models\Laporan;
use App\Models\Dokumen;
use App\Models\ActivityLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

// --- Rute Login & Logout ---
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'authenticate']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// --- Rute Terlindungi ---
Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index']);
    Route::get('/arsip', [DashboardController::class, 'arsip']);
    
    // Upload Dokumen
    Route::post('/upload-dokumen', function (Request $request) {
        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx|max:20480',
            'kategori' => 'required',
            'judul' => 'required'
        ]);
        
        $file = $request->file('file');
        $filename = time() . '_' . $file->getClientOriginalName();
        $file->move(public_path('dokumen'), $filename);
        
        // Simpan ke DB
        Dokumen::create([
            'kategori' => $request->kategori,
            'judul' => $request->judul,
            'file_path' => 'dokumen/' . $filename
        ]);

        // Catat Log UPLOAD
        ActivityLog::create([
            'aksi' => 'UPLOAD', 
            'kategori' => $request->kategori, 
            'judul' => $request->judul
        ]);

        // Update Grafik
        $bulan_sekarang = Carbon::now()->format('M');
        $laporan = Laporan::firstOrCreate(['bulan' => $bulan_sekarang], ['jumlah' => 0]);
        $laporan->increment('jumlah');
        
        return back()->with('success', 'Dokumen berhasil diupload & dicatat!');
    });

    // Hapus Dokumen
    Route::delete('/dokumen/{id}', function ($id) {
        $dokumen = Dokumen::findOrFail($id);
        
        // Catat Log DELETE
        ActivityLog::create([
            'aksi' => 'DELETE', 
            'kategori' => $dokumen->kategori, 
            'judul' => $dokumen->judul
        ]);

        if (file_exists(public_path($dokumen->file_path))) {
            unlink(public_path($dokumen->file_path));
        }
        $dokumen->delete();
        
        return back()->with('success', 'Dokumen dihapus & dicatat!');
    });
});