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
        Schema::table('requests', function (Blueprint $table) {
            $table->string('name')->after('id');
            $table->string('email')->after('name');
            $table->string('phone')->nullable()->after('email');
            $table->string('product_name')->default('N/A')->after('phone');
            $table->integer('quantity')->nullable()->after('product_name');
            $table->string('color')->default('N/A')->after('quantity');
            $table->text('measurements')->nullable()->after('color'); // Store width, length, depth, unit
            $table->text('description')->nullable()->after('measurements');
            $table->enum('type', ['quote', 'contact_us', 'subscribe'])->default('quote')->after('description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->dropColumn(['name', 'email', 'phone', 'product_name', 'quantity', 'color', 'measurements', 'description', 'type']);
        });
    }
};
