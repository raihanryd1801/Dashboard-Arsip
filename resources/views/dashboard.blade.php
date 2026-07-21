<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard NOC | Dankom Mitra Abadi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; color: #334155; }
        .sidebar { background: #0f172a; min-height: 100vh; color: #fff; position: sticky; top: 0; }
        .nav-link { color: #94a3b8; padding: 12px 20px; text-decoration: none; display: block; border-radius: 8px; margin-bottom: 5px; }
        .nav-link:hover, .nav-link.active { color: #fff; background: #1e293b; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
        h2 { font-weight: 600; }
    </style>
</head>
<body>
<div class="container-fluid">
    <div class="row">
        <!-- Sidebar Dinamis -->
        <div class="col-md-2 p-3 sidebar">
            <h5 class="py-3 text-center">Dashboard Menu</h5>
            <nav class="nav flex-column">
                <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="/">Dashboard</a>
                <a class="nav-link {{ request()->is('arsip') ? 'active' : '' }}" href="/arsip">Pusat Dokumen</a>
                
                <!-- Looping Menu Otomatis Berdasarkan Database -->
                @foreach($menu_kategori as $menu)
                    <a class="nav-link {{ request()->is('arsip/' . $menu) ? 'active' : '' }}" href="/arsip/{{ $menu }}">
                        {{ $menu }}
                    </a>
                @endforeach
            </nav>
        </div>

        <!-- Konten -->
        <div class="col-md-10 p-5">
            <div class="d-flex justify-content-between mb-4">
                <h2>Overview Unit</h2>
                <form action="/logout" method="POST">@csrf <button class="btn btn-outline-danger btn-sm">Logout</button></form>
            </div>
            
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

            <!-- BARIS PIE CHARTS -->
            <div class="row g-4 mt-2">
                <div class="col-md-6">
                    <div class="card p-4">
                        <h6>Distribusi Dokumen Berdasarkan Kategori</h6>
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

            <!-- TABEL MONITORING SESI & IP ADDRESS -->
            <div class="card p-4 mt-4">
                <h5>Monitoring Sesi & IP Address Pengguna (Live Sessions)</h5>
                <div class="table-responsive mt-3">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Nama User</th>
                                <th>Email</th>
                                <th>IP Address</th>
                                <th>Aktivitas Terakhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($activeSessions as $session)
                            <tr>
                                <td><strong>{{ $session->name ?? 'Guest / Tamu' }}</strong></td>
                                <td><span class="text-muted">{{ $session->email ?? '-' }}</span></td>
                                <td><span class="badge bg-dark">{{ $session->ip_address ?? 'Unknown IP' }}</span></td>
                                <td>{{ \Carbon\Carbon::createFromTimestamp($session->last_activity)->diffForHumans() }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center text-muted">Tidak ada sesi aktif.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- TABEL RIWAYAT AKTIVITAS DOKUMEN -->
            <div class="card p-4 mt-4">
                <div class="d-flex justify-content-between align-items-center">
                    <h5>Riwayat Aktivitas Dokumen</h5>
                    <form action="/" method="GET" class="d-flex">
                        <input type="text" name="search" class="form-control form-control-sm me-2" 
                               placeholder="Cari dokumen..." value="{{ $search }}">
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
                                <td>
                                    <span class="badge {{ $log->aksi == 'UPLOAD' ? 'bg-success' : 'bg-danger' }}">
                                        {{ $log->aksi }}
                                    </span>
                                </td>
                                <td>{{ $log->judul }}</td>
                                <td><span class="text-muted">{{ $log->kategori }}</span></td>
                                <td>{{ $log->created_at->diffForHumans() }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="4" class="text-center">Data tidak ditemukan.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                    
                    <div class="mt-3">
                        {{ $logs->appends(['search' => $search])->links('pagination::bootstrap-5') }}
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    const ctx = document.getElementById('grafikBulanan').getContext('2d');
    const labels = {!! json_encode($label_bulan) !!};
    const dataJumlah = {!! json_encode($data_jumlah) !!};

    if (labels.length > 0) {
        new Chart(ctx, { 
            type: 'line', 
            data: { 
                labels: labels, 
                datasets: [{ 
                    data: dataJumlah, 
                    borderColor: '#3b82f6', 
                    tension: 0.3, 
                    fill: true, 
                    backgroundColor: 'rgba(59, 130, 246, 0.1)' 
                }] 
            }, 
            options: { 
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { precision: 0 } } } 
            } 
        });
    }

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
</body>
</html>