<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Master Kategori | NOC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; color: #334155; overflow-x: hidden; }
        .wrapper { display: flex; width: 100%; align-items: stretch; }
        .sidebar { background: #0f172a; min-height: 100vh; color: #fff; min-width: 260px; max-width: 260px; transition: margin 0.3s ease-in-out; position: sticky; top: 0; z-index: 1000; }
        .sidebar.hidden { margin-left: -260px; }
        .main-content { width: 100%; padding: 2rem; transition: all 0.3s ease-in-out; min-width: 0; display: flex; flex-direction: column; min-height: 100vh; }
        .nav-link { color: #94a3b8; padding: 12px 20px; text-decoration: none; display: block; border-radius: 8px; margin-bottom: 5px; }
        .nav-link:hover, .nav-link.active { color: #fff; background: #1e293b; }
        .dropdown-toggle-icon::after { content: '\25BC'; float: right; font-size: 10px; margin-top: 7px; transition: transform 0.2s;}
        .nav-link[aria-expanded="true"] .dropdown-toggle-icon::after { transform: rotate(180deg); }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        .footer-wrapper { margin-top: auto; }
    </style>
</head>
<body>

<div class="wrapper">
    <!-- SIDEBAR -->
    <nav class="sidebar p-3" id="sidebar">
        <h5 class="py-3 text-center fw-bold text-white tracking-wide">DATA ARSIP MULTI COMPANY</h5>
        <div class="nav flex-column">
            <a class="nav-link" href="/">Dashboard Utama</a>
            <a class="nav-link" href="/firewall">🛡️ Firewall & Sesi</a>
            <a class="nav-link" href="/converter">Image to PDF Converter</a>
            <a class="nav-link" href="/arsip">Pusat Dokumen</a>
            
            <!-- MENU MASTER KATEGORI AKTIF -->
            <a class="nav-link active fw-bold text-white" href="/kategori">📁 Master Kategori</a>
            
            <div class="mt-3 mb-2 ms-3 text-uppercase text-white-50" style="font-size: 0.75rem; font-weight: bold;">DATA ARSIP PERUSAHAAN</div>
            
            @if(isset($menu_sidebar))
                @foreach($menu_sidebar as $pt => $kategorisMenu)
                    @php $ptId = \Illuminate\Support\Str::slug($pt); @endphp
                    <a class="nav-link text-white-50" data-bs-toggle="collapse" href="#pt-{{ $ptId }}" role="button" aria-expanded="false">
                        <span class="icon-building"></span>
                        <span style="flex:1;">{{ $pt }}</span>
                        <span class="dropdown-toggle-icon"></span>
                    </a>

                    <div class="collapse" id="pt-{{ $ptId }}">
                        <div class="ms-3 mt-2 border-start border-secondary ps-3 mb-3">
                            @foreach($kategorisMenu as $item)
                                <a class="nav-link py-2 text-white-50" href="/arsip/{{ rawurlencode($pt) }}/{{ rawurlencode($item->kategori) }}" style="font-size:13px;">
                                    📄 {{ $item->kategori }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </nav>

    <!-- KONTEN UTAMA -->
    <div class="main-content" id="mainContent">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                <button id="toggleSidebar" class="btn btn-dark" title="Sembunyikan/Tampilkan Sidebar">☰</button>
                <h2 class="mb-0 fw-bold">Master <span class="text-primary">Kategori</span></h2>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#modalProfil">⚙️ Pengaturan Akun</button>
                <form action="/logout" method="POST" class="mb-0">@csrf <button class="btn btn-outline-danger btn-sm">Logout</button></form>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 rounded-3 shadow-sm fw-bold">✅ {{ session('success') }}</div>
        @endif
        @if($errors->any())
            <div class="alert alert-danger border-0 rounded-3 shadow-sm fw-bold">⚠️ {{ $errors->first() }}</div>
        @endif

        <div class="row g-4 mb-4">
            <!-- FORM TAMBAH KATEGORI KIRI -->
            <div class="col-md-4">
                <div class="card p-4 h-100 border-top border-primary border-4">
                    <div class="d-flex align-items-center gap-2 mb-3">
                        <div class="bg-primary text-white rounded p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                            <span style="font-size: 1.2rem;">📁</span>
                        </div>
                        <h5 class="fw-bold mb-0 text-primary">Tambah Kategori</h5>
                    </div>
                    
                    <p class="small text-muted mb-4">Tambahkan kategori dokumen baru ke dalam sistem agar muncul di menu dropdown saat upload arsip.</p>
                    
                    <form action="/kategori" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Nama Kategori Baru</label>
                            <input type="text" name="nama_kategori" class="form-control bg-light" placeholder="Contoh: KONTRAK KERJA" required autofocus>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-bold shadow-sm">➕ Simpan Kategori</button>
                    </form>
                </div>
            </div>

            <!-- TABEL DAFTAR KATEGORI KANAN -->
            <div class="col-md-8">
                <div class="card p-4 h-100">
                    <h5 class="fw-bold mb-4">📑 Daftar Kategori Tersedia</h5>
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th width="10%" class="text-center">No</th>
                                    <th>Nama Kategori</th>
                                    <th width="20%" class="text-end pe-3">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($kategoris as $index => $kat)
                                <tr>
                                    <td class="text-center fw-bold text-muted">{{ $index + 1 }}</td>
                                    <td class="fw-bold text-dark">
                                        {{ $kat->nama_kategori }}
                                    </td>
                                    <td class="text-end pe-3">
                                        <form action="{{ url('/kategori/' . $kat->id) }}" method="POST" class="d-inline">
                                            @csrf 
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger fw-bold" onclick="return confirm('Hapus kategori ini? Dokumen yang sudah menggunakan kategori ini tidak akan terhapus, tapi kategorinya akan hilang dari pilihan dropdown.')">
                                                Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-4">
                                        Belum ada data kategori. Silakan tambahkan di sebelah kiri.
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer-wrapper text-center mt-5 pt-3 border-top text-muted small">
            &copy; {{ date('Y') }} NOC PT. Dankom Mitra Abadi. All rights reserved. | Developed by <strong class="text-primary">raihanryd1801</strong>
        </div>
    </div>
</div>

<!-- MODAL PENGATURAN AKUN -->
<div class="modal fade" id="modalProfil" tabindex="-1" aria-labelledby="modalProfilLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-dark text-white">
                <h5 class="modal-title" id="modalProfilLabel">Pengaturan Akun</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="/update-profile" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama / Username</label>
                        <input type="text" name="name" class="form-control" value="{{ auth()->user()->name }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email (Untuk Login)</label>
                        <input type="email" name="email" class="form-control" value="{{ auth()->user()->email }}" required>
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-bold">Password Baru</label>
                        <input type="password" name="password" class="form-control" placeholder="Kosongkan jika tidak diganti">
                    </div>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('toggleSidebar').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('hidden');
    });
</script>
</body>
</html>