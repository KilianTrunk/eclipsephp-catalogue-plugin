<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pim_group_has_product', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id');
            $table->unsignedBigInteger('group_id');
            $table->integer('sort')->nullable();
            $table->primary(['product_id', 'group_id']);
            $table->foreign('product_id')->references('id')->on('catalogue_products')->onDelete('cascade');
            $table->foreign('group_id')->references('id')->on('pim_group')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pim_group_has_product');
    }
};
