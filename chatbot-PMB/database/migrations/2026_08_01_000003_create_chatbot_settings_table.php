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
        Schema::create('chatbot_settings', function (Blueprint $table) {
            $table->id();
            $table->integer('max_input_character')->default(50);
            $table->integer('max_chat_memory')->default(0);
            $table->integer('max_guest_chat')->default(4);
            $table->integer('top_k')->default(5);
            $table->integer('fetch_k')->default(15);
            $table->float('temperature')->default(0.3);
            $table->text('system_prompt')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chatbot_settings');
    }
};
