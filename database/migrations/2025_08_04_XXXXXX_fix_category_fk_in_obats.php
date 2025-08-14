<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Jalankan migrasi.
     */
    public function up(): void
    {
        // Hapus foreign key lama kalau ada
        try {
            DB::statement('ALTER TABLE `obats` DROP FOREIGN KEY `obats_category_id_foreign`');
        } catch (\Exception $e) {
            // Abaikan error kalau FK tidak ada
        }

        // Pastikan kolom category_id ada
        if (!Schema::hasColumn('obats', 'category_id')) {
            Schema::table('obats', function (Blueprint $table) {
                $table->unsignedBigInteger('category_id')->nullable()->after('stok');
            });
        }

        // Tambahkan foreign key baru
        Schema::table('obats', function (Blueprint $table) {
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->onDelete('restrict'); // tidak bisa hapus kategori kalau dipakai
        });
    }

    /**
     * Rollback migrasi.
     */
    public function down(): void
    {
        Schema::table('obats', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
        });
    }
};
