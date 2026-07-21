<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Arsip | NOC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <!-- Tambahan Flatpickr CSS untuk Kalender Modern -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; color: #334155; }
        .sidebar { background: #0f172a; min-height: 100vh; color: #fff; position: sticky; top: 0; }
        .nav-link { color: #94a3b8; padding: 12px 20px; text-decoration: none; display: block; border-radius: 8px; margin-bottom: 5px; }
        .nav-link:hover, .nav-link.active { color: #fff; background: #1e293b; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
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
                
                @foreach($menu_kategori as $menu)
                    <a class="nav-link {{ request()->is('arsip/' . $menu) ? 'active' : '' }}" href="/arsip/{{ $menu }}">
                        {{ $menu }}
                    </a>
                @endforeach
            </nav>
        </div>

        <div class="col-md-10 p-5">
            <div class="d-flex justify-content-between mb-4">
                <h2>Data Dokumen: <span class="text-primary">{{ $page_title }}</span></h2>
                <form action="/logout" method="POST">@csrf <button class="btn btn-outline-danger btn-sm">Logout</button></form>
            </div>

            @if ($errors->any())
                <div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
            @endif
            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="row mb-4">
                <div class="col-md-{{ $page_title === 'Pusat Dokumen' ? '7' : '12' }}">
                    <div class="card p-4 h-100">
                        <h5>Upload Dokumen Baru</h5>
                        <form id="uploadForm" action="/upload-dokumen" method="POST" enctype="multipart/form-data" class="row g-3 mt-1">
                            @csrf
                            <div class="col-md-4">
                                @if($page_title === 'Pusat Dokumen')
                                    <input class="form-control" list="kategoriOptions" name="kategori" placeholder="Pilih/Ketik Kategori..." required>
                                    <datalist id="kategoriOptions">
                                        @foreach($menu_kategori as $menu)
                                            <option value="{{ $menu }}">
                                        @endforeach
                                    </datalist>
                                @else
                                    <select name="kategori" class="form-select" required style="pointer-events: none; background-color: #e9ecef;">
                                        <option value="{{ $page_title }}" selected>{{ $page_title }}</option>
                                    </select>
                                @endif
                            </div>
                            
                            <div class="col-md-5">
                                <input type="text" name="judul" class="form-control" placeholder="Judul Dokumen" required>
                            </div>
                            
                            <!-- FITUR BARU: Input Tanggal (Text + Flatpickr) -->
                            <div class="col-md-3">
                                <input type="text" id="tanggalDokumen" name="tanggal_dokumen" class="form-control" placeholder="YYYY-MM-DD" required title="Ketik manual atau klik untuk kalender">
                            </div>
                            
                            <div class="col-md-9">
                                <input type="file" id="fileInput" name="file" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx" required>
                            </div>
                            <div class="col-md-3">
                                <button type="submit" class="btn btn-dark w-100">Upload</button>
                            </div>
                        </form>
                    </div>
                </div>

                @if($page_title === 'Pusat Dokumen')
                <div class="col-md-5">
                    <div class="card p-4 h-100">
                        <h6>Statistik Jumlah Dokumen</h6>
                        <div style="height: 150px; width: 100%;">
                            <canvas id="barChartArsip"></canvas>
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <div class="d-flex gap-2 mb-3">
                <input type="text" id="searchJudul" class="form-control" placeholder="Cari Judul Dokumen..." style="max-width: 300px;">
                
                <select id="searchMonth" class="form-select" style="width: 150px;">
                    <option value="">Semua Bulan</option>
                    <option value="01">Januari</option>
                    <option value="02">Februari</option>
                    <option value="03">Maret</option>
                    <option value="04">April</option>
                    <option value="05">Mei</option>
                    <option value="06">Juni</option>
                    <option value="07">Juli</option>
                    <option value="08">Agustus</option>
                    <option value="09">September</option>
                    <option value="10">Oktober</option>
                    <option value="11">November</option>
                    <option value="12">Desember</option>
                </select>

                <select id="searchYear" class="form-select" style="width: 120px;">
                    <option value="">Semua Tahun</option>
                    @for($i = date('Y'); $i >= 2020; $i--)
                        <option value="{{ $i }}">{{ $i }}</option>
                    @endfor
                </select>
            </div>

            <div class="card p-4">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr><th>Kategori</th><th>Judul</th><th>Tanggal Dokumen</th><th class="text-end">Aksi</th></tr>
                    </thead>
                    <tbody id="tableBody">
                        @forelse($dokumen as $dok)
                        @php 
                            $tgl_dok = $dok->tanggal_dokumen ? $dok->tanggal_dokumen : $dok->created_at->format('Y-m-d'); 
                        @endphp
                        <tr data-date="{{ $tgl_dok }}">
                            <td><span class="badge bg-light text-dark border">{{ $dok->kategori }}</span></td>
                            <td>{{ $dok->judul }}</td>
                            <td>
                                <strong>{{ date('d M Y', strtotime($tgl_dok)) }}</strong><br>
                                <small class="text-muted" style="font-size: 0.75rem;">Diunggah: {{ $dok->created_at->format('d/m/Y') }}</small>
                            </td>
                            <td class="text-end">
                                @php $extension = strtolower(pathinfo($dok->file_path, PATHINFO_EXTENSION)); @endphp
                                @if(in_array($extension, ['doc', 'docx']))
                                    <a href="https://docs.google.com/viewer?url={{ urlencode(asset($dok->file_path)) }}&embedded=true" target="_blank" class="btn btn-sm btn-outline-primary">Buka</a>
                                @elseif(in_array($extension, ['xls', 'xlsx']))
                                    <a href="{{ asset($dok->file_path) }}" download class="btn btn-sm btn-outline-success">Download Excel</a>
                                @else
                                    <a href="{{ asset($dok->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">Buka</a>
                                @endif
                                
                                <form action="/dokumen/{{ $dok->id }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin hapus?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="4" class="text-center text-muted">Belum ada dokumen di kategori ini.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Tambahan Flatpickr JS -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
    // --- Inisialisasi Kalender Flatpickr ---
    flatpickr("#tanggalDokumen", {
        allowInput: true, // MENGIZINKAN USER MENGETIK MANUAL
        dateFormat: "Y-m-d", // Format standar database Laravel (Tahun-Bulan-Tanggal)
    });

    @if($page_title === 'Pusat Dokumen')
        const ctxBar = document.getElementById('barChartArsip').getContext('2d');
        const chartLabels = {!! json_encode($chart_labels) !!};
        const chartData = {!! json_encode($chart_data) !!};

        if (chartLabels.length > 0) {
            new Chart(ctxBar, {
                type: 'bar',
                data: {
                    labels: chartLabels,
                    datasets: [{
                        label: 'Jumlah Dokumen',
                        data: chartData,
                        backgroundColor: 'rgba(59, 130, 246, 0.8)',
                        borderRadius: 4
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
    @endif

    document.getElementById('fileInput').addEventListener('change', function() {
        const file = this.files[0];
        const maxSize = 20 * 1024 * 1024;
        if (file && file.size > maxSize) {
            alert('File terlalu besar! Maksimal ukuran adalah 20MB.');
            this.value = '';
        }
    });

    function filterTable() {
        let text = document.getElementById('searchJudul').value.toLowerCase();
        let month = document.getElementById('searchMonth').value;
        let year = document.getElementById('searchYear').value;
        let rows = document.querySelectorAll('#tableBody tr');

        rows.forEach(row => {
            if(row.children.length === 1) return; 
            let rowText = row.innerText.toLowerCase();
            let rowDate = row.getAttribute('data-date'); 
            
            let rowYear = rowDate.substring(0, 4);
            let rowMonth = rowDate.substring(5, 7);

            let matchText = rowText.includes(text);
            let matchMonth = (month === "" || rowMonth === month);
            let matchYear = (year === "" || rowYear === year);
            
            row.style.display = (matchText && matchMonth && matchYear) ? "" : "none";
        });
    }
    
    document.getElementById('searchJudul').addEventListener('keyup', filterTable);
    document.getElementById('searchMonth').addEventListener('change', filterTable);
    document.getElementById('searchYear').addEventListener('change', filterTable);
</script>
</body>
</html>