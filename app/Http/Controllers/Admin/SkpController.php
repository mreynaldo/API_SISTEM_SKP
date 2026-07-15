<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SKP2Ver;
use Illuminate\Support\Facades\Http; 
use App\Models\User;
use Google_Client;

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

    private function kirimPushNotifikasi($fcmToken, $title, $message) 
    {
        $projectId = 'skp-marooners'; 

        $credentialsFilePath = storage_path('app/firebase_credentials.json');

        $client = new Google_Client();
        $client->setAuthConfig($credentialsFilePath);
        $client->addScope('https://www.googleapis.com/auth/firebase.messaging');
        $client->fetchAccessTokenWithAssertion();
        $token = $client->getAccessToken();
        
        $accessToken = $token['access_token'];

        $payload = [
            "message" => [
                "token" => $fcmToken,
                "notification" => [
                    "title" => $title,
                    "body" => $message,
                ],
                "data" => [
                    "title" => $title,
                    "message" => $message,
                ]
            ]
        ];

        $response = Http::withHeaders([
            'Authorization' => 'Bearer ' . $accessToken,
            'Content-Type' => 'application/json',
        ])->post("https://fcm.googleapis.com/v1/projects/{$projectId}/messages:send", $payload);

        return $response->successful();
    }

    public function verifikasi(Request $request, $id)
    {
        $request->validate([
            'status'     => 'required|in:-1,1',
            'keterangan' => 'required_if:status,-1|max:255', 
            'poin' => 'required_if:status,1', 
        ], [
            'keterangan.required_if' => 'Kolom keterangan wajib diisi jika pengajuan ditolak.',
            'poin.required_if' => 'Kolom Poin wajib diisi.',
            'status.required'        => 'Status verifikasi wajib dipilih.',
            'status.in'              => 'Status tidak valid. Hanya menerima 1 (ACC) atau -1 (Tolak).'
        ]);

        $skp = SKP2Ver::findOrFail($id);
        $result = $skp->verifikasi($request->status, $request->keterangan, $request->poin);

        if ($result) {
            $mahasiswa = User::find($skp->user_id);

            if ($mahasiswa && $mahasiswa->fcm_token) {
                if ($request->status == '1') {
                    $title = "Pengajuan SKP Disetujui! 🎉";
                    $message = "Pengajuan '" . $skp->judul . "' telah diverifikasi.";
                } else {
                    $title = "Pengajuan SKP Ditolak ❌";
                    $message = "Pengajuan '" . $skp->judul . "' ditolak. Catatan: " . $request->keterangan;
                }
                
                $this->kirimPushNotifikasi($mahasiswa->fcm_token, $title, $message);
            }
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