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
        Schema::create('spec_keys', function (Blueprint $table) {
            $table->id();
              $table->string('name'); // ram, battery, dll

            $table->foreignId('category_id')
                ->constrained('spec_categories')
                ->cascadeOnDelete();

            $table->string('unit')->nullable(); // GB, mAh, dll
            $table->boolean('is_higher_better')->default(true);

            $table->timestamps();

            $table->unique(['name']); // biar tidak duplicate
    
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('spec_keys');
    }
};
