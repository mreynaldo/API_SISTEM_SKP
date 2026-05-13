<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kegiatan_skp', function (Blueprint $table) { 
            $table->id(); 
            $table->foreignId('kategori_skp_id')->constrained('kategori_skp')->onDelete('cascade');
            $table->string('nama'); 
            $table->integer('poin');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kegiatan_skps');
    }
};
