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
        Schema::table('obats', function (Blueprint $table) {
            // tambahkan category_id, nullable sementara supaya migrasi data lama bisa
            $table->foreignId('category_id')->nullable()->after('id')->constrained('categories')->nullOnDelete();
            // opsional: kalau sebelumnya ada kolom kategori string, bisa dibiarkan dulu dan nanti di-migrate kemudian dihapus
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('obats', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });
    }
};
