<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Arsip Perusahaan Multi Company</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; color: #334155; overflow-x: hidden; }
        .wrapper { display: flex; width: 100%; align-items: stretch; }
        
        /* CSS Sidebar */
        .sidebar { background: #0f172a; min-height: 100vh; color: #fff; min-width: 260px; max-width: 260px; transition: margin 0.3s ease-in-out; position: sticky; top: 0; z-index: 1000; }
        .sidebar.hidden { margin-left: -260px; }
        
        /* CSS Konten Utama */
        .main-content { width: 100%; padding: 2rem; transition: all 0.3s ease-in-out; min-width: 0; display: flex; flex-direction: column; min-height: 100vh; }
        
        /* Navigasi Link */
        .nav-link { color: #94a3b8; padding: 12px 20px; text-decoration: none; display: block; border-radius: 8px; margin-bottom: 5px; }
        .nav-link:hover, .nav-link.active { color: #fff; background: #1e293b; }
        
        /* Icon panah dropdown */
        .dropdown-toggle-icon::after { content: '\25BC'; float: right; font-size: 10px; margin-top: 7px; transition: transform 0.2s;}
        .nav-link[aria-expanded="true"] .dropdown-toggle-icon::after { transform: rotate(180deg); }

        .card { border: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        h2 { font-weight: 600; }
        .footer-wrapper { margin-top: auto; }
    </style>
</head>
<body>

<div class="wrapper">
    <!-- SIDEBAR -->
    <nav class="sidebar p-3" id="sidebar">
        <h5 class="py-3 text-center fw-bold text-white tracking-wide">DATA ARSIP MULTI COMPANY</h5>
        <div class="nav flex-column">
            <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="/">Dashboard Utama</a>
            <a class="nav-link" href="/firewall">Firewall & Sesi</a>
            <!-- TAMBAHAN MENU CONVERTER -->
            <a class="nav-link {{ request()->is('converter') ? 'active' : '' }}" href="/converter">Image to PDF Converter</a>
            <a class="nav-link {{ request()->is('arsip') ? 'active' : '' }}" href="/arsip">Pusat Dokumen</a>

            <!-- MENU MULTI-COMPANY SIDEBAR -->
            <div class="mt-3 mb-2 ms-3 text-uppercase text-white-50" style="font-size: 0.75rem; font-weight: bold;">DATA ARSIP PERUSAHAAN</div>
            
            @foreach($menu_sidebar as $pt => $kategoris)
                @php 
                    $ptId = \Illuminate\Support\Str::slug($pt); 
                    $isPtActive = (isset($active_pt) && $active_pt == $pt); 
                @endphp
                
                <a class="nav-link {{ $isPtActive ? 'text-white active' : 'text-white-50' }}"
                    data-bs-toggle="collapse"
                    href="#pt-{{ $ptId }}"
                    role="button"
                    aria-expanded="{{ $isPtActive ? 'true' : 'false' }}">

                    <span class="icon-building"></span>

                    <span style="flex:1;">
                        {{ $pt }}
                </span>

        <span class="dropdown-toggle-icon"></span>
    </a>
                
                <div class="collapse {{ $isPtActive ? 'show' : '' }}" id="pt-{{ $ptId }}">
                    <div class="ms-3 mt-1 border-start border-secondary ps-2 mb-2">
                        @foreach($kategoris as $item)
                            <a class="nav-link py-1 text-white-50" href="/arsip/{{ rawurlencode($pt) }}/{{ rawurlencode($item->kategori) }}" style="font-size: 0.85rem;">
                                📄 {{ $item->kategori }}
                            </a>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    </nav>

    <!-- KONTEN UTAMA -->
    <div class="main-content" id="mainContent">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div class="d-flex align-items-center gap-3">
                <button id="toggleSidebar" class="btn btn-dark" title="Sembunyikan/Tampilkan Sidebar">☰</button>
                <h2 class="mb-0">Overview Unit</h2>
            </div>
            
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#modalProfil">⚙️ Pengaturan Akun</button>
                <form action="/logout" method="POST" class="mb-0">@csrf <button class="btn btn-outline-danger btn-sm">Logout</button></form>
            </div>
        </div>
        
        @if (session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="row g-4">
            <div class="col-md-4">
                <div class="card p-4 border-start border-primary border-4 h-100">
                    <h6 class="text-muted">Total Sesi Akses Web</h6>
                    <h3 class="mt-2">{{ $user_online }} <small class="fs-6">Sesi</small></h3>
                </div>
            </div>
            <div class="col-md-8">
                <div class="card p-4">
                    <h6>Tren Laporan Bulanan</h6>
                    <div style="height: 250px; width: 100%;">
                        <canvas id="grafikBulanan"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- BARIS KEDUA: PIE CHARTS -->
        <div class="row g-4 mt-2">
            <div class="col-md-6">
                <div class="card p-4">
                    <h6>Distribusi Dokumen Berdasarkan Perusahaan (PT)</h6>
                    <div style="height: 250px; width: 100%; display: flex; justify-content: center;">
                        <canvas id="pieChartKategori"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card p-4">
                    <h6>Distribusi Akses Berdasarkan IP Address</h6>
                    <div style="height: 250px; width: 100%; display: flex; justify-content: center;">
                        <canvas id="pieChartIP"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="card p-4 mt-4 mb-4">
            <div class="d-flex justify-content-between align-items-center">
                <h5>Riwayat Aktivitas Dokumen</h5>
                <form action="/" method="GET" class="d-flex">
                    <input type="text" name="search" class="form-control form-control-sm me-2" placeholder="Cari dokumen..." value="{{ $search }}">
                    <button type="submit" class="btn btn-primary btn-sm">Cari</button>
                </form>
            </div>

            <div class="table-responsive mt-3">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr><th>Aksi</th><th>Dokumen</th><th>Kategori</th><th>Waktu</th></tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                        <tr>
                            <td><span class="badge {{ $log->aksi == 'UPLOAD' ? 'bg-success' : 'bg-danger' }}">{{ $log->aksi }}</span></td>
                            <td>{{ $log->judul }}</td>
                            <td><span class="text-muted">{{ $log->kategori }}</span></td>
                            <td>{{ $log->created_at->diffForHumans() }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center">Data tidak ditemukan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="mt-3">{{ $logs->appends(['search' => $search])->links('pagination::bootstrap-5') }}</div>
            </div>
        </div>

        <div class="footer-wrapper text-center mt-5 pt-3 border-top text-muted small">
            &copy; {{ date('Y') }} NOC PT. Dankom Mitra Abadi. All rights reserved. | Developed by <strong class="text-primary">raihanryd1801</strong>
        </div>

    </div> 
</div> 

<!-- SCRIPT PENDUKUNG -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
    document.getElementById('toggleSidebar').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('hidden');
    });

    // 1. GRAFIK BULANAN (Upload vs Delete)
    const ctx = document.getElementById('grafikBulanan').getContext('2d');
    const labels = {!! json_encode($label_bulan) !!};
    const dataUpload = {!! json_encode($data_upload) !!};
    const dataDelete = {!! json_encode($data_delete) !!};

    if (labels.length > 0) {
        new Chart(ctx, { 
            type: 'line', 
            data: { 
                labels: labels, 
                datasets: [
                    { 
                        label: 'Dokumen Di-upload', 
                        data: dataUpload, 
                        borderColor: '#10b981', 
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        tension: 0.3, 
                        fill: true 
                    },
                    { 
                        label: 'Dokumen Dihapus', 
                        data: dataDelete, 
                        borderColor: '#ef4444', 
                        backgroundColor: 'rgba(239, 68, 68, 0.1)',
                        tension: 0.3, 
                        fill: true 
                    }
                ] 
            }, 
            options: { 
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: true, position: 'bottom' } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } 
            } 
        });
    }

    // 2. PIE / DOUGHNUT CHART DISTRIBUSI PT (INI YANG TADI KETINGGALAN)
    const ctxPie = document.getElementById('pieChartKategori').getContext('2d');
    const pieLabels = {!! json_encode($pie_labels) !!};
    const pieData = {!! json_encode($pie_data) !!};

    if (pieLabels.length > 0) {
        new Chart(ctxPie, {
            type: 'doughnut',
            data: {
                labels: pieLabels,
                datasets: [{
                    data: pieData,
                    backgroundColor: ['#3b82f6', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#ec4899'],
                    borderWidth: 1
                }]
            },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } }
        });
    }

    // 3. PIE / DOUGHNUT CHART IP ADDRESS
    const ctxIP = document.getElementById('pieChartIP').getContext('2d');
    const ipLabels = {!! json_encode($ip_labels) !!};
    const ipData = {!! json_encode($ip_data) !!};
    
    if (ipLabels.length > 0) {
        new Chart(ctxIP, { 
            type: 'doughnut', 
            data: { 
                labels: ipLabels, 
                datasets: [{ 
                    data: ipData, 
                    backgroundColor: ['#0ea5e9', '#6366f1', '#14b8a6', '#f43f5e', '#a855f7'], 
                    borderWidth: 1 
                }] 
            }, 
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'bottom' } } } 
        });
    }
</script>
<!-- ========================================== -->
<!-- MODAL PENGATURAN AKUN (TAMBAHKAN DI SINI) -->
<!-- ========================================== -->
<div class="modal fade" id="modalProfil" tabindex="-1" aria-labelledby="modalProfilLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ url('/profile/update') }}" method="POST">
                @csrf
                @method('PUT') <!-- Sesuaikan dengan route update profil abang (PUT/PATCH/POST) -->
                
                <div class="modal-header">
                    <h5 class="modal-title" id="modalProfilLabel">⚙️ Pengaturan Akun</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" value="{{ auth()->user()->name ?? '' }}" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Email</label>
                        <input type="email" name="email" class="form-control" value="{{ auth()->user()->email ?? '' }}" required>
                    </div>
                    
                    <hr>
                    <p class="text-muted small">Kosongkan jika tidak ingin mengubah password.</p>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Password Baru</label>
                        <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Konfirmasi Password Baru</label>
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Ulangi password baru">
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary btn-sm">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>
</div>
</body>
</html>