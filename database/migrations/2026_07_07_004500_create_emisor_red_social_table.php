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
        Schema::create('emisor_red_social', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('emisor_id');
            $table->unsignedBigInteger('tiporedsocial_id');
            $table->string('name');
            $table->boolean('activo')->default(true);
            
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('emisor_id')
                    ->references('id')->on('emisor');

            $table->foreign('tiporedsocial_id')
                    ->references('id')->on('tipo_red_social');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emisor_red_social');
    }
};
