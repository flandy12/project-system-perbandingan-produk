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
        Schema::create('product_final_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->decimal('specification_score', 5, 2)->default(0);
            $table->decimal('click_score', 5, 2)->default(0);
            $table->decimal('sales_score', 5, 2)->default(0);
            $table->decimal('final_score', 6, 2)->index();
            $table->timestamp('calculated_at');
            $table->timestamps();

            $table->unique('product_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_final_scores');
    }
};
