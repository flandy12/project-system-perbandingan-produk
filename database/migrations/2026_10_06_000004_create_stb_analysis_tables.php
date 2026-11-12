<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_price_observations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->restrictOnDelete();
            $table->date('observed_on');
            $table->decimal('price', 14, 2);
            $table->string('source');
            $table->text('source_reference');
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->timestamps();
            $table->index(['product_id', 'observed_on']);
        });
        Schema::create('stb_analysis_runs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->string('device_type');
            $table->string('sales_source');
            $table->string('status');
            $table->json('snapshot');
            $table->json('result')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('published_by')->nullable()->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stb_analysis_runs');
        Schema::dropIfExists('product_price_observations');
    }
};
