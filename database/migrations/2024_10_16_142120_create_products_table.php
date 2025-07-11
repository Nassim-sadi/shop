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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');
            $table->foreignId('currency_id')->constrained()->onDelete('restrict');

            $table->string('name');
            $table->string('slug')->unique();
            $table->string('description', 255);
            $table->text('long_description');
            $table->boolean('status')->default(false);
            $table->decimal('base_price', 8, 2);
            $table->integer('base_quantity')->default(0);
            $table->decimal('listing_price', 8, 2);
            $table->decimal('weight', 8, 2)->nullable();
            $table->boolean('featured')->default(false);
            $table->string('thumbnail_image_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
