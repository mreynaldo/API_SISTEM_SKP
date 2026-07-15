<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SKP2Ver;
use App\Services\GoogleDriveService;
use Illuminate\Support\Facades\Log;

class SkpController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'judul'            => 'required|max:255',
            'nomor_sertifikat' => 'nullable|max:255',
            'lokasi'           => 'required|max:255',
            'tanggal_mulai'    => 'required|date',
            'tanggal_akhir'    => 'required|date',
            'sertifikat'       => 'required|mimes:pdf|max:10240',
            'kegiatan_skp_id'  => 'required|exists:kegiatan_skp,id',
        ], [
            'sertifikat.mimes' => 'Sertifikat wajib berformat PDF.',
            'sertifikat.max'   => 'Ukuran sertifikat maksimal 10MB.'
        ]);

        $file = $request->file('sertifikat');
        $googleDriveService = new GoogleDriveService();
        
        try {
            $fileId = $googleDriveService->uploadFile(
                $file->getPathname(), 
                $file->getClientOriginalName(), 
                $file->getMimeType()
            );
        } catch (\Exception $e) {
            Log::error("Gagal Upload GDrive: " . $e->getMessage());
            return response()->json([
                'message' => 'Error Asli: ' . $e->getMessage()
               // 'message' => 'Gagal mengunggah sertifikat ke server penyimpanan.'
            ], 500);
        }

        $skp = new SKP2Ver([
            'user_id'          => $request->user()->id, 
            'kegiatan_skp_id'  => $validated['kegiatan_skp_id'],
            'judul'            => $validated['judul'],
            'lokasi'           => $validated['lokasi'],
            'nomor_sertifikat' => $validated['nomor_sertifikat'],
            'tanggal_mulai'    => $validated['tanggal_mulai'],
            'tanggal_akhir'    => $validated['tanggal_akhir'],
            'sertifikat'       => $fileId, 
            'status'           => '0',     
        ]);

        $skp->poin = $skp->calculatePoin();

        if (!$skp->validateTanggal()) {
            $googleDriveService->deleteFile($fileId);
            
            return response()->json([
                'message' => 'Tanggal akhir tidak boleh lebih kecil dari tanggal mulai.'
            ], 422);
        }

        $skp->submit();

        return response()->json([
            'message' => 'Sukses',
            'data'    => $skp
        ], 201);
    }

    public function index(Request $request)
    {
        $user = $request->user();
        $history = $user->getSKPHistory();

        return response()->json([
            'status'  => 'success',
            'message' => 'Berhasil mengambil riwayat SKP mahasiswa.',
            'data'    => $history
        ], 200);
    }

    public function show(Request $request, $id)
    {
        $skp = SKP2Ver::with(['kegiatan.kategori'])
            ->where('user_id', $request->user()->id)
            ->where('id', $id)
            ->first();

        if (!$skp) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Data pengajuan tidak ditemukan atau Anda tidak memiliki akses.'
            ], 404);
        }
        
        $skp->url_sertifikat = 'https://drive.google.com/file/d/' . $skp->sertifikat . '/preview';

        return response()->json([
            'status'  => 'success',
            'message' => 'Detail pengajuan berhasil dimuat.',
            'data'    => $skp
        ], 200);
    }
}