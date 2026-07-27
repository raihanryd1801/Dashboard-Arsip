<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Firewall NOC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; color: #334155; }
        .wrapper { display: flex; width: 100%; align-items: stretch; }
        .sidebar { background: #0f172a; min-height: 100vh; color: #fff; min-width: 260px; max-width: 260px; position: sticky; top: 0; z-index: 1000; }
        .main-content { width: 100%; padding: 2rem; display: flex; flex-direction: column; min-height: 100vh; }
        .nav-link { color: #94a3b8; padding: 12px 20px; text-decoration: none; display: block; border-radius: 8px; margin-bottom: 5px; }
        .nav-link:hover, .nav-link.active { color: #fff; background: #1e293b; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
    </style>
</head>
<body>

<div class="wrapper">
    <!-- SIDEBAR -->
    <nav class="sidebar p-3">
        <h5 class="py-3 text-center fw-bold text-white tracking-wide">DATA ARSIP MULTI COMPANY</h5>
        <div class="nav flex-column">
            <a class="nav-link" href="/">Dashboard Utama</a>
            <a class="nav-link active text-danger fw-bold" href="/firewall">🛡️ Firewall & Sesi</a>
            <a class="nav-link" href="/arsip">Pusat Dokumen</a>
            <!-- TAMBAHAN MENU CONVERTER -->
            <a class="nav-link {{ request()->is('converter') ? 'active' : '' }}" href="/converter">Image to PDF Converter</a>
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
    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2 class="mb-0 fw-bold text-danger">Firewall & Active Sessions</h2>
            <form action="/logout" method="POST" class="mb-0">@csrf <button class="btn btn-outline-danger btn-sm">Logout</button></form>
        </div>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <div class="row g-4">
            <!-- TABEL 1: SESSION MANAGER (TENDANG USER) -->
            <div class="col-md-7">
                <div class="card p-4 h-100 border-start border-danger border-4">
                    <h5 class="fw-bold mb-3 text-danger">Monitor & Drop Sesi Aktif</h5>
                    <p class="small text-muted mb-3">Klik tombol "Drop" untuk menendang perangkat/user yang mencurigakan. Mereka harus login ulang.</p>
                    
                    <div class="table-responsive">
                        <table class="table table-hover align-middle small">
                            <thead class="table-light">
                                <tr><th>User / Pengunjung</th><th>IP Address</th><th>Terakhir Aktif</th><th>Aksi</th></tr>
                            </thead>
                            <tbody>
                                @foreach($activeSessions as $sesi)
                                <tr>
                                    <td class="fw-bold">{{ $sesi->name ?? 'GUEST (Belum Login)' }}</td>
                                    <td><span class="badge bg-secondary">{{ $sesi->ip_address }}</span></td>
                                    <td>{{ \Carbon\Carbon::createFromTimestamp($sesi->last_activity)->diffForHumans() }}</td>
                                    <td>
                                        <form action="/firewall/kick/{{ $sesi->session_id }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Yakin ingin drop / tendang perangkat ini?')">Drop</button>
                                        </form>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- TABEL 2: IP WHITELIST -->
            <div class="col-md-5">
                <div class="card p-4 h-100 border-start border-success border-4">
                    <h5 class="fw-bold mb-3 text-success">IP Allow-List (Whitelist)</h5>
                    <p class="small text-danger fw-bold bg-danger-subtle p-2 rounded">PENTING: Jika daftar ini diisi, maka HANYA IP dalam daftar ini yang bisa membuka web. Pastikan IP Anda sendiri dimasukkan lebih dulu!</p>
                    
                    <form action="/firewall/allow" method="POST" class="mb-4 bg-light p-3 rounded">
                        @csrf
                        <div class="mb-2">
                            <input type="text" name="ip_address" class="form-control form-control-sm" placeholder="Contoh: 192.168.99.10" required>
                        </div>
                        <div class="mb-2">
                            <input type="text" name="keterangan" class="form-control form-control-sm" placeholder="Keterangan (Admin / Kantor / Dll)">
                        </div>
                        <button type="submit" class="btn btn-sm btn-success w-100">Izinkan IP (Allow)</button>
                    </form>

                    <div class="table-responsive">
                        <table class="table align-middle small">
                            <thead class="table-light"><tr><th>IP Diizinkan</th><th>Keterangan</th><th>Cabut</th></tr></thead>
                            <tbody>
                                @forelse($firewallIps as $ip)
                                <tr>
                                    <td class="fw-bold">{{ $ip->ip_address }}</td>
                                    <td>{{ $ip->keterangan }}</td>
                                    <td>
                                        <form action="/firewall/revoke/{{ $ip->id }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Hapus akses IP ini?')">✖</button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="3" class="text-center text-muted">Belum ada IP yang dibatasi. Web saat ini terbuka untuk publik/semua IP.</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <!-- ROW BAWAH: FAIL2BAN & UNBAN -->
            <div class="row g-4 mt-1">
                <!-- TABEL 3: KONFIGURASI FAIL2BAN -->
                <div class="col-md-8">
                    <div class="card p-4 h-100 border-start border-warning border-4">
                        <h5 class="fw-bold mb-3 text-warning text-dark">⚙️ Konfigurasi Keamanan Fail2ban (Brute-force)</h5>
                        <p class="small text-muted mb-4">Atur parameter otomatis pemblokiran IP jika ada pengunjung yang mencoba menebak password.</p>
                        
                        <form action="/firewall/fail2ban" method="POST" class="row g-3 bg-light p-3 rounded">
                            @csrf
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">Max Retry</label>
                                <input type="number" name="maxretry" class="form-control" value="{{ $fail2ban->maxretry }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold small">Ban Time</label>
                                <select name="bantime" class="form-select" required>
                                    <option value="600" {{ $fail2ban->bantime == 600 ? 'selected' : '' }}>10 Menit</option>
                                    <option value="3600" {{ $fail2ban->bantime == 3600 ? 'selected' : '' }}>1 Jam</option>
                                    <option value="86400" {{ $fail2ban->bantime == 86400 ? 'selected' : '' }}>1 Hari</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold small">Ignore IP (Kebal)</label>
                                <input type="text" name="ignoreip" class="form-control" value="{{ $fail2ban->ignoreip }}" placeholder="127.0.0.0">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-warning w-100 fw-bold">Update</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- TABEL 4: UNBAN IP -->
                <div class="col-md-4">
                    <div class="card p-4 h-100 border-start border-info border-4">
                        <h5 class="fw-bold mb-3 text-info text-dark">🔓 Lepas Blokir (Unban IP)</h5>
                        <p class="small text-muted mb-3">Buka blokir IP rekan yang terlanjur ditendang oleh sistem karena salah password.</p>
                        
                        <form action="/firewall/unban" method="POST" class="bg-light p-3 rounded">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-bold small">IP Address yang Diblokir</label>
                                <input type="text" name="ip_address" class="form-control form-control-sm" placeholder="Contoh: 192.168.99.100" required>
                            </div>
                            <button type="submit" class="btn btn-sm btn-info w-100 fw-bold text-white">Unban IP Sekarang</button>
                        </form>
                    </div>
                </div>
            </div>
            </div>
            
        </div>

    </div>
</div>
</body>
</html>