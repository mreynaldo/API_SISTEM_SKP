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
}