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
        Schema::create('product_specs', function (Blueprint $table) {
            $table->id();
           $table->foreignId('product_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->foreignId('spec_key_id')
                ->constrained('spec_keys')
                ->cascadeOnDelete();

            $table->decimal('value', 10, 2); // angka (sudah normalize)

            $table->timestamps();

            // 1 produk hanya boleh punya 1 spec_key
            $table->unique(['product_id', 'spec_key_id']);

            // index untuk performa compare
            $table->index('product_id');
            $table->index('spec_key_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_specs');
    }
};
