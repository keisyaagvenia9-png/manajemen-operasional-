<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Membuat tabel transaksi kuitansi.
     */
    public function up(): void
    {
        Schema::create('transaksis', function (Blueprint $table) {
            $table->id();

            // Tim pemilik transaksi
            $table->foreignId('team_id')
                ->constrained('teams')
                ->cascadeOnDelete();

            // Petugas yang mencatat transaksi
            $table->foreignId('user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();

            // Identitas kuitansi
            $table->string('nomor_kuitansi')
                ->nullable()
                ->unique();

            // Data pembayaran
            $table->string('nama_pelanggan');
            $table->date('tanggal');
            $table->text('keterangan');
            $table->unsignedBigInteger('nominal');

            // Metode dan status pembayaran
            $table->enum('metode_pembayaran', [
                'tunai',
                'transfer'
            ])->default('tunai');

            $table->enum('status', [
                'belum_lunas',
                'lunas'
            ])->default('belum_lunas');

            // Waktu pencatatan
            $table->timestamps();
        });
    }

    /**
     * Menghapus tabel jika migration dibatalkan.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaksis');
    }
};
