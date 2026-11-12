<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_import_batches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('filename');
            $table->string('file_hash', 64);
            $table->string('status')->default('preview');
            $table->json('rows');
            $table->json('summary')->nullable();
            $table->timestamp('applied_at')->nullable();
            $table->timestamps();
        });
        Schema::table('products', function (Blueprint $table) {
            $table->string('product_code')->nullable()->unique();
        });
        Schema::table('product_sales_stats', function (Blueprint $table) {
            $table->string('source')->nullable();
            $table->text('source_reference')->nullable();
            $table->string('verification_status')->default('unverified');
            $table->boolean('is_simulated')->default(false);
            $table->foreignId('sales_import_batch_id')->nullable()->constrained('sales_import_batches')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('product_sales_stats', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sales_import_batch_id');
            $table->dropColumn(['source', 'source_reference', 'verification_status', 'is_simulated']);
        });
        Schema::table('products', fn (Blueprint $table) => $table->dropColumn('product_code'));
        Schema::dropIfExists('sales_import_batches');
    }
};
