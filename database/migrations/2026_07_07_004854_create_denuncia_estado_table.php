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
        Schema::create('denuncia_estado', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('denuncia_id');
            $table->unsignedBigInteger('estado_id');
            $table->boolean('activo')->default(true);
            
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('denuncia_id')
                    ->references('id')->on('denuncia');

            $table->foreign('estado_id')
                    ->references('id')->on('estado');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('denuncia_localidad');
    }
};
