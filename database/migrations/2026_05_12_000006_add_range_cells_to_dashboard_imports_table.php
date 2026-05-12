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
            $table->string('header_start_cell', 8)->default('A1')->after('sheet_name');
            $table->string('data_end_cell', 8)->nullable()->after('header_start_cell');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dashboard_imports', function (Blueprint $table) {
            $table->dropColumn(['header_start_cell', 'data_end_cell']);
        });
    }
};
