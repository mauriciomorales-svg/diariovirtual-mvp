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
            $table->uuid('id')->primary();
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('source_hash')->unique(); // sha256 de URL original para evitar duplicados
            $table->string('excerpt', 255);
            $table->text('content')->nullable();
            $table->string('image_url');
            $table->boolean('is_external')->default(false);
            $table->string('external_url')->nullable();
            $table->enum('status', ['draft', 'scheduled', 'published'])->default('draft');
            $table->timestamp('published_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            // Índices para rendimiento
            $table->index(['status', 'published_at']);
            $table->index('slug');
            $table->index('published_at');
            $table->index('source_hash');
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
