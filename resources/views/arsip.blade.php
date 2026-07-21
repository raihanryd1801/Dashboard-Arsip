<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Arsip | NOC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
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
        <!-- Sidebar yang sudah disamakan -->
        <div class="col-md-2 p-3 sidebar">
            <h5 class="py-3 text-center">NOC Portal</h5>
            <nav class="nav flex-column">
                <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="/">Dashboard</a>
                <a class="nav-link {{ request()->is('arsip') ? 'active' : '' }}" href="/arsip">Arsip Dokumen</a>
                <a class="nav-link {{ request()->is('sop') ? 'active' : '' }}" href="/sop">SOP Unit</a>
                <a class="nav-link {{ request()->is('loca') ? 'active' : '' }}" href="/loca">LOCA</a>
            </nav>
        </div>

        <div class="col-md-10 p-5">
            <div class="d-flex justify-content-between mb-4">
                <h2>Arsip Dokumen</h2>
                <form action="/logout" method="POST">@csrf <button class="btn btn-outline-danger btn-sm">Logout</button></form>
            </div>

            <!-- Error Alerts -->
            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="card p-4 mb-4">
                <h5>Upload Dokumen Baru</h5>
                <form id="uploadForm" action="/upload-dokumen" method="POST" enctype="multipart/form-data" class="row g-2 mt-2">
                    @csrf
                    <div class="col-md-3"><input type="text" name="kategori" class="form-control" placeholder="Kategori (SOP/LOCA)" required></div>
                    <div class="col-md-4"><input type="text" name="judul" class="form-control" placeholder="Judul Dokumen" required></div>
                    <div class="col-md-3">
                        <input type="file" id="fileInput" name="file" class="form-control" accept=".pdf,.doc,.docx,.xls,.xlsx" required>
                    </div>
                    <div class="col-md-2"><button type="submit" class="btn btn-dark w-100">Upload</button></div>
                </form>
            </div>

            <div class="d-flex gap-2 mb-3">
                <input type="text" id="searchJudul" class="form-control" placeholder="Cari Judul...">
                <input type="date" id="searchDate" class="form-control" style="width: 200px;">
            </div>

            <div class="card p-4">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr><th>Kategori</th><th>Judul</th><th>Tanggal Upload</th><th class="text-end">Aksi</th></tr>
                    </thead>
                    <tbody id="tableBody">
                        @foreach($dokumen as $dok)
                        <tr data-date="{{ date('Y-m-d', strtotime($dok->created_at)) }}">
                            <td><span class="badge bg-light text-dark border">{{ $dok->kategori }}</span></td>
                            <td>{{ $dok->judul }}</td>
                            <td>{{ $dok->created_at->format('d M Y') }}</td>
                            <td class="text-end">
                                <a href="{{ asset($dok->file_path) }}" target="_blank" class="btn btn-sm btn-outline-primary">Buka</a>
                                <form action="/dokumen/{{ $dok->id }}" method="POST" class="d-inline">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger" onclick="return confirm('Yakin hapus?')">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    // File Size Validation Script
    document.getElementById('fileInput').addEventListener('change', function() {
        const file = this.files[0];
        const maxSize = 20 * 1024 * 1024; // 20MB

        if (file && file.size > maxSize) {
            alert('File terlalu besar! Maksimal ukuran adalah 20MB.');
            this.value = ''; // Reset input
        }
    });

    // Filtering Script
    function filterTable() {
        let text = document.getElementById('searchJudul').value.toLowerCase();
        let date = document.getElementById('searchDate').value;
        let rows = document.querySelectorAll('#tableBody tr');

        rows.forEach(row => {
            let rowText = row.innerText.toLowerCase();
            let rowDate = row.getAttribute('data-date');
            let matchText = rowText.includes(text);
            let matchDate = date === "" || rowDate === date;
            row.style.display = (matchText && matchDate) ? "" : "none";
        });
    }
    document.getElementById('searchJudul').addEventListener('keyup', filterTable);
    document.getElementById('searchDate').addEventListener('change', filterTable);
</script>
</body>
</html>