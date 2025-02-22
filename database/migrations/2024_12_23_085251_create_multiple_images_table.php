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
        Schema::create('multiple_images', function (Blueprint $table) {
            $table->id();
            $table->string('related_id');
            $table->string('related_type');           
            $table->text('image_url');
            $table->string('identifier')->nullable();           
            $table->timestamps();

            $table->index(['related_id', 'related_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('multiple_images');
    }
};
