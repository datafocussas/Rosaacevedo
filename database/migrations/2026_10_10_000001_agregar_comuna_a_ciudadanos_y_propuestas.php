<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// La comuna (división 2024) se pide en el registro y en el buzón aunque no haya catálogo de barrios.
// Si la persona elige barrio, la comuna sale del barrio. Las filas existentes se completan desde su barrio.
return new class extends Migration
{
    public function up(): void
    {
        foreach (['ciudadanos' => 'ciudadano', 'propuestas_ciudadanas' => 'prop'] as $tabla => $prefijo) {
            Schema::table($tabla, function (Blueprint $table) use ($prefijo) {
                $table->unsignedSmallInteger('comuna_id')->nullable()->after('barrio_id')->comment('Comuna 2024 elegida o derivada del barrio');
                $table->index('comuna_id', 'ix_'.$prefijo.'_comuna');
                $table->foreign('comuna_id', 'fk_'.$prefijo.'_comuna')->references('id')->on('territorio_comuna');
            });

            DB::table($tabla)->whereNotNull('barrio_id')->whereNull('comuna_id')->orderBy('id')->each(function ($fila) use ($tabla) {
                $comuna = DB::table('territorio_barrio')->where('id', $fila->barrio_id)->value('comuna_2024_id');
                DB::table($tabla)->where('id', $fila->id)->update(['comuna_id' => $comuna]);
            });
        }
    }

    public function down(): void
    {
        foreach (['ciudadanos' => 'ciudadano', 'propuestas_ciudadanas' => 'prop'] as $tabla => $prefijo) {
            Schema::table($tabla, function (Blueprint $table) use ($prefijo) {
                $table->dropForeign('fk_'.$prefijo.'_comuna');
                $table->dropIndex('ix_'.$prefijo.'_comuna');
                $table->dropColumn('comuna_id');
            });
        }
    }
};
