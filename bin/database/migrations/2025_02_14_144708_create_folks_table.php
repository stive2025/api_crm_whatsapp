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
        Schema::create('folks', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->string('relationship')->nullable();
            $table->foreignId('contact_id');
            $table->foreign('contact_id')
                ->references('id')
                ->on('contacts');
            $table->foreignId('contact_rel_id');
            $table->foreign('contact_rel_id')
                ->references('id')
                ->on('contacts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('folks');
    }
};
