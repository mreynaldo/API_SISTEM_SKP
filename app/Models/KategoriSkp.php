<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KategoriSkp extends Model
{
    protected $table = 'kategori_skp';

    protected $fillable = [
        'nama'
    ];

    public function kegiatan()
    {
        return $this->hasMany(KegiatanSkp::class, 'kategori_skp_id');
    }

    public static function getKegiatanList()
    {
        return self::with('kegiatan')->get();
    }

    public function addKegiatan(array $dataKegiatan)
    {
        return $this->kegiatan()->create($dataKegiatan);
    }

}
