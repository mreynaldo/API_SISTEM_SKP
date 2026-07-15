<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http; 

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'name' => ['required', 'string', 'max:255'],
            'no_id' => ['required', 'string', 'max:30', 'unique:users,no_id'],
            'no_hp' => ['required', 'string', 'max:20'],
            'password' => ['required', 'string', 'min:8'], 
        ]);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.api.token'),
            ])->get(config('services.api.base_url') . '/mahasiswa/nim/' . $request->no_id);

            $data = $response->json();
            $mahasiswa = $data['data'][0] ?? null;

            if (!$mahasiswa || empty($mahasiswa['nim'])) {
                return response()->json([
                    'message' => 'NPM tidak ditemukan. Silakan periksa kembali atau hubungi admin.'
                ], 404);
            }

            // Validasi Whitelist Prodi Fasilkom
            $allowedProdi = [
                'informatika',
                'sistem informasi',
                // <-- Kalau nanti prodi nambah, tinggal daftarin di sini!
            ];

            $prodiKampus = strtolower($mahasiswa['prodi']);

            if (!in_array($prodiKampus, $allowedProdi)) {
                return response()->json([
                    'message' => 'Registrasi ditolak. Aplikasi ini khusus untuk mahasiswa Fakultas Ilmu Komputer.'
                ], 403); 
            }

            $user = User::create([
                'no_id' => $mahasiswa['nim'],
                'name' => $mahasiswa['nama_mahasiswa'],
                'email' => $mahasiswa['email'],
                'program_studi' => $mahasiswa['prodi'],
                'no_hp' => $request->no_hp,
                'password' => Hash::make($request->password), 
            ]);

            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Registrasi mahasiswa berhasil',
                'user_data' => $user,
                'access_token' => $token,
                'token_type' => 'Bearer'
            ], 201);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Gagal menghubungi API Kampus: ' . $e->getMessage()
            ], 500);
        }
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Kredensial tidak valid. Email atau Password salah.'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login berhasil',
            'user_data' => $user,
            'access_token' => $token,
            'token_type' => 'Bearer'
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout berhasil, sesi telah diakhiri'
        ], 200);
    }

    public function saveFcmToken(Request $request)
    {
        $request->validate([
            'fcm_token' => 'required|string',
        ]);

        $user = auth()->user();
        // Update kolom fcm_token untuk user yang sedang login
        $user->update(['fcm_token' => $request->fcm_token]);

        return response()->json([
            'status' => 'success',
            'message' => 'FCM Token berhasil disimpan.'
        ], 200);
    }

    public function cekNpm($nim)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . config('services.api.token'),
            ])->get(config('services.api.base_url') . '/mahasiswa/nim/' . $nim);

            $data = $response->json();
            $mahasiswa = $data['data'][0] ?? null;

            if (!$mahasiswa || empty($mahasiswa['nim'])) {
                return response()->json([
                    'message' => 'NPM tidak ditemukan.'
                ], 404);
            }

            $allowedProdi = [
                'informatika',
                'sistem informasi',
            ];

            $prodiKampus = strtolower($mahasiswa['prodi']);

            if (!in_array($prodiKampus, $allowedProdi)) {
                return response()->json([
                    'message' => 'Akses ditolak. Aplikasi ini khusus untuk mahasiswa Fakultas Ilmu Komputer.'
                ], 403); 
            }

            return response()->json([
                'message' => 'Data ditemukan',
                'data' => [
                    'nim' => $mahasiswa['nim'],
                    'nama' => $mahasiswa['nama_mahasiswa'],
                    'email' => $mahasiswa['email'],
                    'prodi' => $mahasiswa['prodi']
                ]
            ], 200);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Gagal menghubungi server kampus.'
            ], 500);
        }
    }
}