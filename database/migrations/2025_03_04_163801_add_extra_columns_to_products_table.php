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
        Schema::table('products', function (Blueprint $table) {
            $table->string('title_2')->nullable()->after('title'); // Additional title
            $table->text('description_2')->nullable()->after('description'); // Additional description
            $table->json('faqs')->nullable()->after('description_2');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['title_2', 'description_2', 'faqs']);
        });
    }
};
