<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SuratMasuk extends Model
{
    protected $table = 'surat_masuk';

    protected $fillable = [
        'no_surat',
        'tgl_ns',
        'no_asal',
        'tgl_no_asal',
        'pengirim',
        'penerima',
        'perihal',
        'token_lampiran',
        'dibaca',
        'disposisi',
        'user_id',
        'tgl_sm'
    ];

    protected $casts = [
        'dibaca' => 'boolean',
        'disposisi' => 'boolean',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function lampiran()
    {
        return $this->hasMany(Lampiran::class, 'token_lampiran', 'token_lampiran');
    }
}
