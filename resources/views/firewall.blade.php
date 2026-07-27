<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Firewall NOC</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        /* --- CSS UTAMA (KONSISTEN DENGAN DASHBOARD) --- */
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

        .card { border: none; border-radius: 12px; box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); overflow: hidden; }
        h2 { font-weight: 600; }
        .footer-wrapper { margin-top: auto; }

        /* --- CSS TAMBAHAN KHUSUS TABEL FIREWALL (CYBER STYLE) --- */
        .font-mono { font-family: 'Courier New', Courier, monospace; font-size: 0.85rem; font-weight: 600; }
        .card-header-cyber { background-color: #f8fafc; border-bottom: 1px solid #e2e8f0; padding: 12px 16px; font-weight: 700; text-transform: uppercase; font-size: 0.85rem; letter-spacing: 0.5px; }
        .border-left-red { border-left: 4px solid #ef4444 !important; }
        .border-left-green { border-left: 4px solid #10b981 !important; }
        .border-left-yellow { border-left: 4px solid #f59e0b !important; }
        .table>thead { text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.5px; color: #64748b; background-color: #f1f5f9; }
    </style>
</head>
<body>

<div class="wrapper">
    <!-- SIDEBAR (SESUAI REQUEST) -->
    <nav class="sidebar p-3" id="sidebar">
        <h5 class="py-3 text-center fw-bold text-white tracking-wide">DATA ARSIP MULTI COMPANY</h5>
        <div class="nav flex-column">
            <a class="nav-link {{ request()->is('/') ? 'active' : '' }}" href="/">Dashboard Utama</a>
            <a class="nav-link active text-danger fw-bold" href="/firewall">🛡️ Firewall & Sesi</a>
            <a class="nav-link {{ request()->is('arsip') ? 'active' : '' }}" href="/arsip">Pusat Dokumen</a>
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
        <div class="d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom border-secondary-subtle">
            <div>
                <h2 class="mb-0 fw-bold text-danger text-uppercase" style="letter-spacing: 1px;">Security & Firewall Console</h2>
                <small class="text-muted font-mono">SYSTEM: ACTIVE | ENGINE: FAIL2BAN & IPTABLES</small>
            </div>
            <form action="/logout" method="POST" class="mb-0">
                @csrf <button class="btn btn-outline-danger btn-sm fw-bold">TERMINATE SESSION</button>
            </form>
        </div>

        @if(session('success'))
            <div class="alert alert-success border-0 rounded-3 font-mono">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger border-0 rounded-3 font-mono">{{ session('error') }}</div>
        @endif

        <div class="row g-4">
            <!-- TABEL 1: SESSION MANAGER -->
            <div class="col-md-7">
                <div class="card h-100 border-left-red">
                    <div class="card-header-cyber text-danger">ACTIVE SESSIONS MONITOR</div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr><th>Identity</th><th>IP Address</th><th>Last Seen</th><th class="text-end pe-3">Action</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($activeSessions as $sesi)
                                    <tr>
                                        <td class="fw-bold ps-3">{{ $sesi->name ?? 'GUEST / UNAUTH' }}</td>
                                        <td class="font-mono text-primary">{{ $sesi->ip_address }}</td>
                                        <td class="font-mono text-muted">{{ \Carbon\Carbon::createFromTimestamp($sesi->last_activity)->diffForHumans() }}</td>
                                        <td class="text-end pe-3">
                                            <form action="/firewall/kick/{{ $sesi->session_id }}" method="POST">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger fw-bold" style="font-size: 0.7rem;" onclick="return confirm('Drop connection for this session?')">DROP</button>
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

            <!-- TABEL 2: IP WHITELIST -->
            <div class="col-md-5">
                <div class="card h-100 border-left-green">
                    <div class="card-header-cyber text-success">ACCESS CONTROL LIST (ACL)</div>
                    <div class="card-body">
                        <div class="alert alert-danger font-mono p-2 mb-3" style="font-size: 0.75rem;">
                            <strong>WARNING:</strong> DEFAULT DENY APPLIES IF LIST IS POPULATED. ADD YOUR IP FIRST.
                        </div>
                        
                        <form action="/firewall/allow" method="POST" class="mb-3 d-flex gap-2">
                            @csrf
                            <input type="text" name="ip_address" class="form-control form-control-sm font-mono" placeholder="IP Address" required>
                            <input type="text" name="keterangan" class="form-control form-control-sm" placeholder="Desc (Opt)">
                            <button type="submit" class="btn btn-sm btn-success fw-bold">ALLOW</button>
                        </form>

                        <div class="table-responsive border rounded-2">
                            <table class="table align-middle mb-0">
                                <thead><tr><th class="ps-3">Allowed IP</th><th>Desc</th><th class="text-end pe-3">Revoke</th></tr></thead>
                                <tbody>
                                    @forelse($firewallIps as $ip)
                                    <tr>
                                        <td class="font-mono fw-bold text-success ps-3">{{ $ip->ip_address }}</td>
                                        <td class="small text-muted">{{ $ip->keterangan ?? 'N/A' }}</td>
                                        <td class="text-end pe-3">
                                            <form action="/firewall/revoke/{{ $ip->id }}" method="POST">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-2 fw-bold" onclick="return confirm('Revoke this IP?')">DEL</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr><td colspan="3" class="text-center font-mono text-muted py-3">ACL IS EMPTY. GLOBAL ACCESS ALLOWED.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABEL 3: KONFIGURASI FAIL2BAN -->
            <div class="col-md-7">
                <div class="card h-100 border-left-yellow">
                    <div class="card-header-cyber text-warning" style="color: #d97706 !important;">FAIL2BAN IDS CONFIGURATION</div>
                    <div class="card-body">
                        <form action="/firewall/fail2ban" method="POST" class="row g-3">
                            @csrf
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-muted" style="font-size: 0.75rem;">Max Retry Limits</label>
                                <input type="number" name="maxretry" class="form-control form-control-sm font-mono" value="{{ $fail2ban->maxretry }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-muted" style="font-size: 0.75rem;">Penalty Duration</label>
                                <select name="bantime" class="form-select form-select-sm font-mono" required>
                                    <option value="600" {{ $fail2ban->bantime == 600 ? 'selected' : '' }}>600s (10 MIN)</option>
                                    <option value="3600" {{ $fail2ban->bantime == 3600 ? 'selected' : '' }}>3600s (1 HR)</option>
                                    <option value="86400" {{ $fail2ban->bantime == 86400 ? 'selected' : '' }}>86400s (24 HR)</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold text-muted" style="font-size: 0.75rem;">Global Ignore IP</label>
                                <input type="text" name="ignoreip" class="form-control form-control-sm font-mono" value="{{ $fail2ban->ignoreip }}" placeholder="127.0.0.1">
                            </div>
                            <div class="col-12 mt-4 text-end">
                                <button type="submit" class="btn btn-sm btn-warning fw-bold" style="background-color: #f59e0b; color: white; border: none;">APPLY & RESTART ENGINE</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- TABEL 4: DAFTAR IP TERBANNED & UNBAN -->
            <div class="col-md-5">
                <div class="card h-100 border-left-red bg-white">
                    <div class="card-header-cyber text-danger">QUARANTINE / BANNED LIST</div>
                    <div class="card-body d-flex flex-column">
                        
                        <div class="table-responsive border rounded-2 mb-3 flex-grow-1" style="max-height: 140px; overflow-y: auto;">
                            <table class="table table-sm align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-3">Target IP</th>
                                        <th class="text-end pe-3">Command</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($bannedIps as $bannedIp)
                                    <tr>
                                        <td class="font-mono text-danger fw-bold ps-3">
                                            <span class="badge bg-danger rounded-1 me-2 py-1">BLOCKED</span>{{ $bannedIp }}
                                        </td>
                                        <td class="text-end pe-3">
                                            <form action="/firewall/unban" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="ip_address" value="{{ $bannedIp }}">
                                                <button type="submit" class="btn btn-outline-success fw-bold py-0 px-2" style="font-size: 0.7rem;" onclick="return confirm('Release IP {{ $bannedIp }} from quarantine?')">UNBAN</button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="2" class="text-center text-muted font-mono py-4">STATUS: CLEAN. NO BLOCKED TARGETS.</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        <form action="/firewall/unban" method="POST" class="mt-auto">
                            @csrf
                            <label class="form-label fw-bold text-muted" style="font-size: 0.75rem;">Manual Unban Override</label>
                            <div class="input-group input-group-sm">
                                <input type="text" name="ip_address" class="form-control font-mono" placeholder="Target IP Address..." required>
                                <button type="submit" class="btn btn-dark fw-bold">EXECUTE</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>