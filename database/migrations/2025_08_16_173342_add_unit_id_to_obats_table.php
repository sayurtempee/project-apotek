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
            if (!Schema::hasColumn('obats', 'unit_id')) {
                $table->unsignedBigInteger('unit_id')->nullable()->after('stok');

                $table->foreign('unit_id')->references('id')->on('units')
                    ->onDelete('set null');
            }
        });
    }

    public function down(): void
    {
        Schema::table('obats', function (Blueprint $table) {
            if (Schema::hasColumn('obats', 'unit_id')) {
                $table->dropForeign(['unit_id']);
                $table->dropColumn('unit_id');
            }
        });
    }
};
