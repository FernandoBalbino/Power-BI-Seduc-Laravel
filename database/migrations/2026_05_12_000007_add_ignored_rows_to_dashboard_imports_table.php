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
        Schema::table('dashboard_imports', function (Blueprint $table) {
            $table->json('ignored_rows')->nullable()->after('data_end_cell');
            $table->json('excluded_columns')->nullable()->after('ignored_rows');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dashboard_imports', function (Blueprint $table) {
            $table->dropColumn(['ignored_rows', 'excluded_columns']);
        });
    }
};
