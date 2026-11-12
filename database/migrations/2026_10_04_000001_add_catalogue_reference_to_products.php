<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('price', 15, 2)->nullable()->change();
            $table->string('comparison_type')->nullable();
            $table->json('reference_metadata')->nullable();
        });
    }

    public function down(): void
    {
        // Keep unknown prices nullable: rollback must not replace them with invented values.
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['comparison_type', 'reference_metadata']);
        });
    }
};
