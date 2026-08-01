<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('knowledge_chunks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('knowledge_base_id')->constrained('knowledge_bases')->onDelete('cascade');
            $table->text('chunk_text');
            $table->string('kategori');
            $table->integer('chunk_order')->default(0);
            $table->json('embedding')->nullable();
            $table->timestamps();
        });

        // Try creating vector column if pgvector extension is available in Postgres
        try {
            DB::statement('CREATE EXTENSION IF NOT EXISTS vector');
            DB::statement('ALTER TABLE knowledge_chunks ALTER COLUMN embedding TYPE vector USING embedding::text::vector');
            DB::statement('CREATE INDEX IF NOT EXISTS knowledge_chunks_embedding_hnsw_idx ON knowledge_chunks USING hnsw (embedding vector_cosine_ops)');
        } catch (\Throwable $e) {
            // Fallback gracefully if pgvector extension is not enabled in local Postgres instance yet
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge_chunks');
    }
};
