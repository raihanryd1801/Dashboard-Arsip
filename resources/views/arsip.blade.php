<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Arsip | NOC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
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
            <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="/">Dashboard Utama</a>
            <a class="nav-link" href="/firewall">🛡️ Firewall & Sesi</a>
            <a class="nav-link {{ request()->is('arsip') ? 'active' : '' }}" href="/arsip">Pusat Dokumen</a>
            <a class="nav-link {{ request()->is('converter') ? 'active' : '' }}" href="/converter">Image to PDF Converter</a>
            
            <div class="mt-3 mb-2 ms-3 text-uppercase text-white-50" style="font-size: 0.75rem; font-weight: bold;">DATA ARSIP PERUSAHAAN</div>
            
            @foreach($menu_sidebar as $pt => $kategoris)
                @php
                    $ptId = \Illuminate\Support\Str::slug($pt);
                    $isPtActive = isset($active_pt) && $active_pt == $pt;
                @endphp

                <a class="nav-link {{ $isPtActive ? 'text-white active' : 'text-white-50' }}"
                    data-bs-toggle="collapse"
                    href="#pt-{{ $ptId }}"
                    role="button"
                    aria-expanded="{{ $isPtActive ? 'true' : 'false' }}">

                    <span class="icon-building"></span>
                    <span style="flex:1;">{{ $pt }}</span>
                    <span class="dropdown-toggle-icon"></span>
                </a>

                <div class="collapse {{ $isPtActive ? 'show' : '' }}" id="pt-{{ $ptId }}">
                    <div class="ms-3 mt-2 border-start border-secondary ps-3 mb-3">
                        @foreach($kategoris as $item)
                            @php
                                $isKatActive = isset($page_title) && $page_title == $item->kategori && $isPtActive;
                            @endphp

                            <a class="nav-link py-2 {{ $isKatActive ? 'active text-white' : 'text-white-50' }}"
                                href="/arsip/{{ rawurlencode($pt) }}/{{ rawurlencode($item->kategori) }}"
                                style="font-size:13px;">
                                <span class="icon-file"></span>
                                {{ $item->kategori }}
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
                <h2 class="mb-0">Data Dokumen: <span class="text-primary">{{ $page_title }}</span></h2>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#modalProfil">⚙️ Pengaturan Akun</button>
                <form action="/logout" method="POST" class="mb-0">@csrf <button class="btn btn-outline-danger btn-sm">Logout</button></form>
            </div>
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
                        
                        <!-- INPUT PERUSAHAAN (Bisa Pilih dari Datalist / Dropdown atau Ketik Manual, Tanpa Required Kaku) -->
                        <!-- INPUT PERUSAHAAN (PILIH DARI LIST ATAU KETIK MANUAL) -->
<div class="col-md-6">
    <label class="form-label fw-bold">Nama Perusahaan (PT)</label>
    
    <div class="input-group">
        <!-- 1. Dropdown untuk memilih PT yang sudah ada -->
        <select id="selectPt" class="form-select" onchange="document.getElementById('inputPt').value = this.value;">
            <option value="">-- Pilih dari Daftar PT --</option>
            @if(isset($listPt))
                @foreach($listPt as $pt)
                    <option value="{{ $pt }}">{{ $pt }}</option>
                @endforeach
            @endif
        </select>

        <!-- 2. Input teks untuk ketik manual atau edit hasil pilihan dropdown -->
        <input type="text" id="inputPt" name="perusahaan" class="form-control" placeholder="Atau ketik nama PT baru..." required>
    </div>
    <div class="form-text text-muted" style="font-size: 0.75rem;">Pilih dari dropdown di kiri, atau ketik langsung di kolom kanan jika PT baru.</div>
</div>
                        
                        <!-- Input Kategori -->
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Kategori Dokumen</label>
                            @if($page_title === 'Pusat Dokumen')
                                <input class="form-control" list="kategoriOptions" name="kategori" placeholder="Pilih/Ketik Kategori..." required>
                                <datalist id="kategoriOptions">
                                    <option value="Berita Acara">
                                    <option value="Invoice">
                                    <option value="PKS">
                                    <option value="Kwitansi">
                                    <option value="Sertifikat">
                                </datalist>
                            @else
                                <input type="text" name="kategori" class="form-control bg-light" value="{{ $page_title }}" readonly style="pointer-events: none;">
                            @endif
                        </div>

                        <!-- Baris Input File dkk -->
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Judul Dokumen</label>
                            <input type="text" name="judul" class="form-control" placeholder="Judul Dokumen" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-bold">Tanggal Dokumen</label>
                            <input type="text" id="tanggalDokumen" name="tanggal_dokumen" class="form-control" placeholder="YYYY-MM-DD" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label fw-bold">File Dokumen</label>
                            <input type="file" id="fileInput" name="file" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx" required>
                        </div>
                        <div class="col-md-12 mt-2">
                            <button type="submit" class="btn btn-dark w-100">Upload Dokumen</button>
                        </div>
                    </form>
                </div>
            </div>

            @if($page_title === 'Pusat Dokumen')
            <div class="col-md-5">
                <div class="card p-4 h-100">
                    <h6>Statistik Jumlah Dokumen</h6>
                    <div style="height: 150px; width: 100%;"><canvas id="barChartArsip"></canvas></div>
                </div>
            </div>
            @endif
        </div>

        <!-- Filter Pencarian -->
        <div class="d-flex gap-2 mb-3">
            <input type="text" id="searchJudul" class="form-control" placeholder="Cari Judul Dokumen..." style="max-width: 300px;">
            <select id="searchMonth" class="form-select" style="width: 150px;">
                <option value="">All Month</option>
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
                <option value="">All Years</option>
                @for($i = date('Y'); $i >= 2020; $i--)
                    <option value="{{ $i }}">{{ $i }}</option>
                @endfor
            </select>
        </div>

        <div class="card p-4 mb-4">
            <table class="table table-hover align-middle">
                <thead class="table-light">
                    <tr><th>Nama PT. & Kategori</th><th>Judul</th><th>Tanggal Dokumen</th><th class="text-end">Aksi</th></tr>
                </thead>
                <tbody id="tableBody">
                    @forelse($dokumen as $dok)
                    @php $tgl_dok = $dok->tanggal_dokumen ? $dok->tanggal_dokumen : $dok->created_at->format('Y-m-d'); @endphp
                    <tr data-date="{{ $tgl_dok }}">
                        <td>
                            <span class="badge bg-dark">{{ $dok->perusahaan }}</span><br>
                            <span class="badge bg-light text-dark border mt-1">{{ $dok->kategori }}</span>
                        </td>
                        <td>{{ $dok->judul }}</td>
                        <td>
                            <strong>{{ date('d M Y', strtotime($tgl_dok)) }}</strong><br>
                            <small class="text-muted" style="font-size: 0.75rem;">Diunggah: {{ $dok->created_at->format('d/m/Y') }}</small>
                        </td>
                        <td class="text-end">
                            @php 
                                $extension = strtolower(pathinfo($dok->file_path, PATHINFO_EXTENSION));
                                $safeUrl = url($dok->file_path);
                            @endphp

                            @if(in_array($extension, ['doc', 'docx']))
                                <a href="{{ $safeUrl }}" download class="btn btn-sm btn-outline-primary">Download Word</a>
                            @elseif(in_array($extension, ['xls', 'xlsx']))
                                <a href="{{ $safeUrl }}" download class="btn btn-sm btn-outline-success">Download Excel</a>
                            @else
                                <a href="{{ $safeUrl }}" target="_blank" class="btn btn-sm btn-outline-primary">Buka</a>
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
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<script>
    document.getElementById('toggleSidebar').addEventListener('click', function() {
        document.getElementById('sidebar').classList.toggle('hidden');
    });

    flatpickr("#tanggalDokumen", { allowInput: true, dateFormat: "Y-m-d" });

    @if($page_title === 'Pusat Dokumen')
        const ctxBar = document.getElementById('barChartArsip').getContext('2d');
        const chartLabels = {!! json_encode($chart_labels) !!};
        const chartDatasets = {!! json_encode($chart_datasets) !!};
        
        if (chartLabels.length > 0) {
            new Chart(ctxBar, { 
                type: 'bar', 
                data: { 
                    labels: chartLabels, 
                    datasets: chartDatasets 
                }, 
                options: { 
                    responsive: true, 
                    maintainAspectRatio: false, 
                    plugins: { 
                        legend: { 
                            display: true, 
                            position: 'bottom',
                            labels: { boxWidth: 12, font: { size: 11 } } 
                        } 
                    }, 
                    scales: { 
                        x: { stacked: false }, 
                        y: { beginAtZero: true, ticks: { precision: 0 } } 
                    } 
                } 
            });
        }
    @endif

    document.getElementById('fileInput').addEventListener('change', function() {
        const file = this.files[0];
        if (file && file.size > 20 * 1024 * 1024) { alert('File terlalu besar! Maksimal ukuran adalah 20MB.'); this.value = ''; }
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
            
            row.style.display = (rowText.includes(text) && (month === "" || rowMonth === month) && (year === "" || rowYear === year)) ? "" : "none";
        });
    }
    document.getElementById('searchJudul').addEventListener('keyup', filterTable);
    document.getElementById('searchMonth').addEventListener('change', filterTable);
    document.getElementById('searchYear').addEventListener('change', filterTable);
</script>
</body>
</html>