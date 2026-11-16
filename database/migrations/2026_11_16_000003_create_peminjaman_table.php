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
        Schema::create('peminjaman', function (Blueprint $table) {
            $table->id();
            $table->foreignId('anggota_id')->constrained('anggota')->restrictOnDelete();
            $table->foreignId('buku_id')->constrained('buku')->restrictOnDelete();
            $table->dateTime('dipinjam_pada');
            $table->date('jatuh_tempo')->nullable();
            $table->dateTime('dikembalikan_pada')->nullable();
            $table->enum('status', ['dipinjam', 'kembali'])->default('dipinjam');
            $table->timestamps();

            $table->index(['anggota_id', 'dipinjam_pada']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peminjaman');
    }
};
