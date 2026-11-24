<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Banners dinámicos: cada banner puede tener varias fotos (colecciones escritorio y movil sin límite de una) que
// rotan solas. Aquí solo se agrega cada cuántos segundos cambia la foto; las fotos ya cargadas siguen igual.
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->unsignedTinyInteger('segundos_rotacion')->default(6)->after('orden')
                ->comment('Segundos que se ve cada foto antes de pasar a la siguiente (3 a 30)');
        });
    }

    public function down(): void
    {
        Schema::table('banners', function (Blueprint $table) {
            $table->dropColumn('segundos_rotacion');
        });
    }
};
