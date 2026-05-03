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
        Schema::create('category_weights', function (Blueprint $table) {
            $table->id();

            $table->foreignId('category_id')
                ->constrained('spec_categories')
                ->cascadeOnDelete();

            $table->decimal('weight', 5, 2)->default(1.00);

            $table->unique('category_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_weights');
    }
};
