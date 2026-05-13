<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('skp2ver', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('kegiatan_skp_id')->constrained('kegiatan_skp')->onDelete('cascade');
            $table->string('judul')->comment('judul kegiatan');;
            $table->string('lokasi');
            $table->string('nomor_sertifikat');
            $table->date('tanggal_mulai');
            $table->date('tanggal_akhir');
            $table->string('sertifikat'); 
            $table->enum('status', ['-1', '0', '1'])->default('0')->comment('-1: ditolak, 0: proses, 1: diterima');
            $table->string('keterangan')->nullable();
            $table->integer('poin')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('skp2ver');
    }
};