<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KegiatanSkp extends Model
{
    protected $table = 'kegiatan_skp';

    protected $fillable = [
        'kategori_skp_id', 
        'nama', 
        'poin'
    ];

    public function kategori()
    {
        return $this->belongsTo(KategoriSkp::class, 'kategori_skp_id');
    }

    public function getDefaultPoin()
    {
        return $this->poin;
    }

    public function updatePoin($newPoin)
    {
        return $this->update(['poin' => $newPoin]);
    }
}
