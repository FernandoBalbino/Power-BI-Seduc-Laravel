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
        Schema::create('dashboard_columns', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dashboard_id')->constrained()->cascadeOnDelete();
            $table->string('original_name');
            $table->string('normalized_name');
            $table->string('friendly_name')->nullable();
            $table->string('type', 40);
            $table->boolean('is_filterable')->default(false);
            $table->boolean('is_chartable')->default(true);
            $table->boolean('is_required')->default(false);
            $table->unsignedInteger('position');
            $table->timestamps();

            $table->unique(['dashboard_id', 'normalized_name']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dashboard_columns');
    }
};
