<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('cart_item', 'cart_items');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('cart_items', 'cart_item');
    }
};