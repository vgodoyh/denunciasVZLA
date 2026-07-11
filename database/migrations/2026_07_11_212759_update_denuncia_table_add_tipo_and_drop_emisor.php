<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('denuncia', function (Blueprint $table) {
            $table->foreignId('tipodenuncia_id')
                ->nullable()
                ->after('titular')
                ->constrained('tipo_denuncia');
        });
    }

    public function down(): void
    {
        Schema::table('denuncia', function (Blueprint $table) {
            $table->dropForeign(['tipodenuncia_id']);
            $table->dropColumn('tipodenuncia_id');
        });
    }
};