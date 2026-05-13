<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SKP2Ver;

class SkpController extends Controller
{
    public function index()
    {
        $antrean = SKP2Ver::where('status', '0')
            ->with(['user', 'kegiatan.kategori'])
            ->orderBy('created_at', 'asc') 
            ->get();

        return response()->json([
            'status'  => 'success',
            'message' => 'Berhasil memuat antrean pengajuan SKP.',
            'data'    => $antrean
        ], 200);
    }

    public function show($id)
    {
        $skp = SKP2Ver::with(['user', 'kegiatan.kategori'])->findOrFail($id);
        $skp->url_sertifikat = 'https://drive.google.com/file/d/' . $skp->sertifikat . '/preview';

        return response()->json([
            'status'  => 'success',
            'message' => 'Detail pengajuan berhasil dimuat.',
            'data'    => $skp
        ], 200);
    }

    public function verifikasi(Request $request, $id)
    {
        $request->validate([
            'status'     => 'required|in:-1,1',
            'keterangan' => 'required_if:status,-1|max:255', 
        ], [
            'keterangan.required_if' => 'Kolom keterangan wajib diisi jika pengajuan ditolak.',
            'status.required'        => 'Status verifikasi wajib dipilih.',
            'status.in'              => 'Status tidak valid. Hanya menerima 1 (ACC) atau -1 (Tolak).'
        ]);

        $skp = SKP2Ver::findOrFail($id);
        $result = $skp->verifikasi($request->status, $request->keterangan);

        if ($result) {
            return response()->json([
                'status'  => 'success',
                'message' => 'Status pengajuan berhasil diperbarui.',
                'data'    => $skp
            ], 200);
        }

        return response()->json([
            'status'  => 'error',
            'message' => 'Gagal memperbarui status.'
        ], 500);
    }
}