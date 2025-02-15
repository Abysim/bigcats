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
        Schema::create('news', function (Blueprint $table) {
            $table->id();
            $table->date('date');
            $table->string('slug');
            $table->string('title');
            $table->text('content');
            $table->string('source_name')->nullable();
            $table->string('source_url', 1024)->nullable();
            $table->string('image')->nullable();
            $table->string('image_caption', 1024)->nullable();
            $table->string('author')->nullable();
            $table->boolean('is_original')->default(false);
            $table->boolean('is_published')->default(false);
            $table->unique(['date', 'slug']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('news');
    }
};
