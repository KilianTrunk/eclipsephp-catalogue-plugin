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
        Schema::table('catalogue_product_data', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('product_id')
                ->constrained('catalogue_categories')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
        Schema::table('catalogue_products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('catalogue_products', function (Blueprint $table) {
            $table->foreignId('category_id')->nullable()->after('name')
                ->constrained('catalogue_categories')
                ->cascadeOnUpdate()
                ->nullOnDelete();
        });
        Schema::table('catalogue_product_data', function (Blueprint $table) {
            $table->dropConstrainedForeignId('category_id');
        });
    }
};
