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
        Schema::create('dashboard_relationships', function (Blueprint $table) {
            $table->id();
            $table->foreignId('dashboard_id')->constrained()->cascadeOnDelete();
            $table->foreignId('base_column_id')->constrained('dashboard_columns')->cascadeOnDelete();
            $table->foreignId('related_column_id')->nullable()->constrained('dashboard_columns')->cascadeOnDelete();
            $table->string('aggregation', 20);
            $table->string('relationship_type', 20);
            $table->timestamps();

            $table->index(['dashboard_id', 'base_column_id']);
            $table->index(['dashboard_id', 'related_column_id']);
            $table->unique(
                ['dashboard_id', 'base_column_id', 'related_column_id', 'aggregation'],
                'dashboard_relationships_unique_pair'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dashboard_relationships');
    }
};
