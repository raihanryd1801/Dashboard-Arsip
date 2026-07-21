<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Personil;
use App\Models\Dokumen;
use App\Models\Laporan;
use App\Models\User;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ... (isi data seperti yang tadi)
        User::create([
            'name' => 'Admin NOC', 
            'email' => 'admin@dankom.co.id', 
            'password' => bcrypt('rahasia123')
        ]);

        Personil::insert([
            ['nama' => 'Andi Susanto', 'status' => 'On Duty'],
            ['nama' => 'Budi Pratama', 'status' => 'On Duty'],
            ['nama' => 'Melisa Latsar', 'status' => 'Off Duty'],
        ]);

        Dokumen::insert([
            ['kategori' => 'SOP', 'judul' => 'SOP Penanganan Gangguan', 'file_path' => 'dokumen/sop.pdf'],
            ['kategori' => 'LOCA', 'judul' => 'LOCA Aktivasi Pelanggan', 'file_path' => 'dokumen/loca.pdf'],
        ]);

        Laporan::insert([
            ['bulan' => 'Jan', 'jumlah' => 45], 
            ['bulan' => 'Feb', 'jumlah' => 60],
            ['bulan' => 'Mar', 'jumlah' => 55], 
            ['bulan' => 'Apr', 'jumlah' => 80],
            ['bulan' => 'Mei', 'jumlah' => 75],
        ]);
    }
}