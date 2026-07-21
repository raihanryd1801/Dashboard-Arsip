<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Dokumen extends Model
{
    protected $fillable = [
        'kategori', 
        'judul', 
        'file_path', 
        'tanggal_dokumen' // <--- Tambahkan ini
    ];
}
