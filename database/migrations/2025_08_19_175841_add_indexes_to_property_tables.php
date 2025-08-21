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
        Schema::table('pim_property', function (Blueprint $table) {
            $table->index(['is_active', 'is_global']);
            $table->index('is_filter');
        });
        Schema::table('pim_property_value', function (Blueprint $table) {
            $table->index(['property_id', 'sort']);
        });
        Schema::table('pim_product_type_has_property', function (Blueprint $table) {
            $table->index(['product_type_id', 'sort']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pim_property', function (Blueprint $table) {
            $table->dropIndex('pim_property_is_active_is_global_index');
            $table->dropIndex('pim_property_is_filter_index');
        });
        Schema::table('pim_property_value', function (Blueprint $table) {
            $table->dropIndex('pim_property_value_property_id_sort_index');
        });
        Schema::table('pim_product_type_has_property', function (Blueprint $table) {
            $table->dropIndex('pim_product_type_has_property_product_type_id_sort_index');
        });
    }
};
