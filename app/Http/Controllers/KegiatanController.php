<?php

namespace App\Http\Controllers;

use App\Models\KegiatanSkp;
use Illuminate\Http\Request;

class KegiatanController extends Controller
{
    
    public function store(Request $request)
    {
        $request->validate([
            'kategori_skp_id' => 'required|exists:kategori_skp,id',
            'nama'            => 'required|string|max:255',
            'poin'            => 'required|integer|min:1'
        ], [
            'kategori_skp_id.required' => 'Kategori wajib dipilih.',
            'kategori_skp_id.exists'   => 'Kategori tidak valid atau tidak ditemukan di sistem.',
            'nama.required'            => 'Nama kegiatan wajib diisi.',
            'poin.required'            => 'Poin kegiatan wajib diisi.',
            'poin.integer'             => 'Poin harus berupa angka.'
        ]);
        
        $kegiatan = KegiatanSkp::create([
            'kategori_skp_id' => $request->kategori_skp_id,
            'nama'            => $request->nama,
            'poin'            => $request->poin
        ]);

        return response()->json([
            'message' => 'Data kegiatan berhasil ditambahkan.',
            'data'    => $kegiatan
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'kategori_skp_id' => 'required|exists:kategori_skp,id',
            'nama'            => 'required|string|max:255',
            'poin'            => 'required|integer|min:1'
        ], [
            'kategori_skp_id.required' => 'Kategori wajib dipilih.',
            'kategori_skp_id.exists'   => 'Kategori tidak valid atau tidak ditemukan di sistem.',
            'nama.required'            => 'Nama kegiatan wajib diisi.',
            'poin.required'            => 'Poin kegiatan wajib diisi.',
            'poin.integer'             => 'Poin harus berupa angka.'
        ]);

        $kegiatan = KegiatanSkp::findOrFail($id);
        $kegiatan->update([
            'kategori_skp_id' => $request->kategori_skp_id,
            'nama'            => $request->nama,
            'poin'            => $request->poin
        ]);

        return response()->json([
            'message' => 'Data kegiatan berhasil diperbarui.',
            'data'    => $kegiatan
        ], 200);
    }

    public function destroy($id)
    {
        $kegiatan = KegiatanSkp::findOrFail($id);
        $kegiatan->delete();

        return response()->json([
            'message' => 'Data kegiatan berhasil dihapus.'
        ], 200);
    }
}