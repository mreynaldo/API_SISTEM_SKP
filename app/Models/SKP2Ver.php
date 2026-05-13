<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SKP2Ver extends Model
{
    protected $table = 'skp2ver';

    protected $fillable = [
        'user_id',
        'kegiatan_skp_id',
        'judul',
        'lokasi',
        'nomor_sertifikat',
        'tanggal_mulai',
        'tanggal_akhir',
        'sertifikat',
        'status',
        'keterangan',
        'poin'
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function kegiatan()
    {
        return $this->belongsTo(KegiatanSkp::class, 'kegiatan_skp_id');
    }

    public function submit()
    {
        return $this->save();
    }

    public function validateTanggal()
    {
        if (strtotime($this->tanggal_akhir) < strtotime($this->tanggal_mulai)) {
            return false;
        }
        return true;
    }

    public function calculatePoin()
    {
        if ($this->kegiatan) {
            return $this->kegiatan->poin;
        }
        return 0;
    }

    public function verifikasi($status, $catatan)
    {
        $this->status = $status;
        $this->keterangan = $catatan;
        return $this->save();
    }
}