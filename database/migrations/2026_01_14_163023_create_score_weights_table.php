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
        Schema::create('score_weights', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique(); // specification, click, sales
            $table->decimal('weight', 4, 3); // total = 1.000
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('score_weights');
    }
};
