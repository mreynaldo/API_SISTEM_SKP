<?php

namespace App\Http\Controllers;

use App\Models\KategoriSkp;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    public function index()
    {
        $data = KategoriSkp::getKegiatanList();

        return response()->json([
            'status'  => 'success',
            'message' => 'Daftar kategori berhasil diambil.',
            'data'    => $data
        ], 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255|unique:kategori_skp,nama'
        ], [
            'nama.required' => 'Nama kategori wajib diisi.',
            'nama.unique'   => 'Nama kategori sudah terdaftar.',
            'nama.max'      => 'Nama kategori maksimal 255 karakter.'
        ]);
        
        $kategori = KategoriSkp::create([
            'nama' => $request->nama
        ]);

        return response()->json([
            'message' => 'Data kategori berhasil ditambahkan.',
            'data'    => $kategori
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'nama' => 'required|string|max:255|unique:kategori_skp,nama,' . $id
        ], [
            'nama.required' => 'Nama kategori wajib diisi.',
            'nama.unique'   => 'Nama kategori sudah terdaftar.',
            'nama.max'      => 'Nama kategori maksimal 255 karakter.'
        ]);

        $kategori = KategoriSkp::findOrFail($id);
        $kategori->update([
            'nama' => $request->nama
        ]);

        return response()->json([
            'message' => 'Data kategori berhasil diperbarui.',
            'data'    => $kategori
        ], 200);
    }

    public function destroy($id)
    {
        $kategori = KategoriSkp::findOrFail($id);
        $kategori->delete();

        return response()->json([
            'message' => 'Data kategori berhasil dihapus.'
        ], 200);
    }
}