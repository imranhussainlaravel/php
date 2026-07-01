<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * SEO: add an editable meta_title (SEO <title>) to categories and products. Nullable so
     * existing rows are unaffected; the frontend falls back to its auto-generated title when empty.
     */
    public function up(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->string('meta_title')->nullable()->after('meta_description');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->string('meta_title')->nullable()->after('meta_description');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('meta_title');
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('meta_title');
        });
    }
};
