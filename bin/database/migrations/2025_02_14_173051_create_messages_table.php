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
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('id_message_wp')->nullable();
            $table->text('body')->nullable();
            $table->integer('ack')->nullable();
            $table->string('from_me')->nullable();
            $table->string('to')->nullable();
            $table->string('media_type')->nullable();
            $table->string('media_path')->nullable();
            $table->string('timestamp_wp')->nullable();
            $table->integer('is_private')->nullable();
            $table->string('state');
            $table->foreignId('deleted_by')->nullable();
            $table->foreign('deleted_by')
                ->references('id')
                ->on('users');
            $table->foreignId('created_by');
            $table->foreign('created_by')
                ->references('id')
                ->on('users');
            $table->foreignId('chat_id');
            $table->foreign('chat_id')
                ->references('id')
                ->on('chats');
            $table->foreignId('tag_id')->nullable();
            $table->foreign('tag_id')
                ->references('id')
                ->on('tags');
        });
    }
    
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};