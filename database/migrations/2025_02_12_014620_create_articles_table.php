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
        Schema::create('articles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable();
            $table->string('slug');
            $table->tinyInteger('priority')->default(0);
            $table->string('title');
            $table->text('content')->default('');
            $table->string('image')->nullable();
            $table->string('image_caption')->nullable();
            $table->boolean('is_published')->default(false);
            $table->unique(['parent_id', 'slug']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('articles');
    }
};
