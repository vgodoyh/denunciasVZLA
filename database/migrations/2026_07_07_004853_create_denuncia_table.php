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
        Schema::create('denuncia', function (Blueprint $table) {
            $table->id();
            $table->date('fecha');
            $table->string('url');
            $table->string('titular');
            $table->text('contenido');
            $table->text('observacion')->nullable();
            $table->unsignedBigInteger('emisor_id');
            $table->unsignedBigInteger('emisorredsocial_id');
            $table->unsignedBigInteger('user_id');
            $table->string('estatus')->default('Pendiente');
            
            $table->softDeletes();
            $table->timestamps();
            
            $table->foreign('emisor_id')
                    ->references('id')->on('emisor');

            $table->foreign('emisorredsocial_id')
                    ->references('id')->on('emisor_red_social');

            $table->foreign('user_id')
                    ->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('denuncia');
    }
};
