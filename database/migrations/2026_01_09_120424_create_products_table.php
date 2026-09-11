<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title', 255);
            $table->string('slug', 255);
            $table->text('description');
            $table->string('cover_image')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->unsignedInteger('quantity');
            $table->foreignId('seller_id')->constrained("users")->cascadeOnDelete();
            $table->boolean('is_active')->default(true);
            $table->integer('sold_count')->default(0);
            $table->decimal('rating_avg', 2, 1)->default(0);
            $table->integer('rating_count')->default(0);
            $table->timestamps();

            $table->unique(['seller_id', 'slug']);
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
