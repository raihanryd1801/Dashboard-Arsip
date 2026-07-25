<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\FirewallIp;

class IpFirewall
{
    public function handle(Request $request, Closure $next)
    {
        $allowedIps = FirewallIp::pluck('ip_address')->toArray();
        
        // Jika tabel Whitelist ada isinya DAN IP user saat ini tidak terdaftar
        if (count($allowedIps) > 0 && !in_array($request->ip(), $allowedIps)) {
            // Jika dia mencoba mengakses halaman selain login/logout, tendang!
            if (!$request->is('login') && !$request->is('logout') && !auth()->check()) {
                return redirect('/login')->withErrors(['email' => 'FIREWALL: Akses ditolak dari IP ' . $request->ip()]);
            }
            
            // Jika sudah terlanjur login tapi IP tidak di-whitelist, langsung paksa logout & blokir
            if (auth()->check()) {
                auth()->logout();
                abort(403, 'FIREWALL BLOCK: Sesi Anda dihentikan. IP Anda (' . $request->ip() . ') DIANGGAP ILLEGAL OLEH KAMI.');
            }
        }

        return $next($request);
    }
}