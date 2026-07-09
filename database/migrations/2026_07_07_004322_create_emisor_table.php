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
        Schema::create('emisor', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedBigInteger('tipoemisor_id');
            $table->boolean('activo')->default(true);
            
            $table->softDeletes();
            $table->timestamps();

            $table->foreign('tipoemisor_id')
                    ->references('id')->on('tipo_emisor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emisor');
    }
};
