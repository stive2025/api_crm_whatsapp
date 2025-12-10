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
        Schema::create('transfers', function (Blueprint $table) {
            // El usuario con ID 5 pidió transferencia del chat 34, actualmente se encuentra en estado PENDING
            $table->id();
            $table->foreignId('user_id');
            $table->foreign('user_id')
                ->references('id')
                ->on('users');
            $table->foreignId('contact_id');
            $table->foreign('contact_id')
                ->references('id')
                ->on('contacts');
            $table->integer('transfered_by')->nullable();
            $table->string('type');
            $table->text('message')->nullable();
            $table->string('state');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transfers');
    }
};
