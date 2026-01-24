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
        Schema::create('product_specification_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->foreignId('specification_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->decimal('raw_value', 10, 2);
            $table->decimal('normalized_score', 5, 2);
            $table->timestamps();
            $table->unique(['product_id', 'specification_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_specification_scores');
    }
};
