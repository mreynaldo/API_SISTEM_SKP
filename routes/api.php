<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\KategoriController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\Mahasiswa\SkpController as MahasiswaSkpController;
use App\Http\Controllers\Admin\SkpController as AdminSkpController;


Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/kategori', [KategoriController::class, 'index']);
Route::get('/cek-npm/{nim}', [AuthController::class, 'cekNpm']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/save-fcm-token', [AuthController::class, 'saveFcmToken']);

    // --- RUTE KHUSUS ADMIN  ---
    Route::middleware('role.admin')->prefix('admin')->group(function () {
        Route::post('/kategori', [KategoriController::class, 'store']);
        Route::put('/kategori/{id}', [KategoriController::class, 'update']);
        Route::delete('/kategori/{id}', [KategoriController::class, 'destroy']);

        Route::post('/kegiatan', [KegiatanController::class, 'store']);
        Route::put('/kegiatan/{id}', [KegiatanController::class, 'update']);
        Route::delete('/kegiatan/{id}', [KegiatanController::class, 'destroy']);

        Route::get('/skp', [AdminSkpController::class, 'index']);
        Route::get('/skp/{id}', [AdminSkpController::class, 'show']);
        Route::put('/skp/{id}/verifikasi', [AdminSkpController::class, 'verifikasi']);
        
    });

    // --- RUTE KHUSUS MAHASISWA ---
    Route::middleware('role.mahasiswa')->prefix('mahasiswa')->group(function () {
        Route::post('/skp', [MahasiswaSkpController::class, 'store']);
        Route::get('/skp', [MahasiswaSkpController::class, 'index']);
        Route::get('/skp/{id}', [MahasiswaSkpController::class, 'show']);
    });

});