<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
   // Tambahkan baris ini
    protected $fillable = ['aksi', 'kategori', 'judul'];
}
