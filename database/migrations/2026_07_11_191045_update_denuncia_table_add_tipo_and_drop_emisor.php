<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('denuncia_palabra_clave', function (Blueprint $table) {
            $table->id();
            $table->foreignId('denuncia_id')->constrained('denuncia')->cascadeOnDelete();
            $table->foreignId('palabras_claves_id')->constrained('palabras_claves')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['denuncia_id', 'palabras_claves_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('denuncia_palabra_clave');
    }
};