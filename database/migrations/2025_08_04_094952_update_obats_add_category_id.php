<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus FK lama
        DB::statement('ALTER TABLE obats DROP FOREIGN KEY obats_category_id_foreign');

        // Buat FK baru dengan aturan delete restrict
        Schema::table('obats', function (Blueprint $table) {
            $table->foreign('category_id')
                ->references('id')
                ->on('categories')
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('obats', function (Blueprint $table) {
            $table->dropForeign(['category_id']);
        });
    }
};
