<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up(): void
  {
    Schema::table('products', function (Blueprint $table) {
      $table->renameColumn('discount_percentage', 'discount_amount');
    });

    Schema::table('products', function (Blueprint $table) {
      $table->decimal('discount_amount', 10, 2)->default(0)->change();
    });
  }

  public function down(): void
  {
    Schema::table('products', function (Blueprint $table) {
      $table->decimal('discount_amount', 5, 2)->default(0)->change();
    });

    Schema::table('products', function (Blueprint $table) {
      $table->renameColumn('discount_amount', 'discount_percentage');
    });
  }
};