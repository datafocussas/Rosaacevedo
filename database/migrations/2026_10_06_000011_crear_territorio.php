<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Territorio: siete comunas + corregimiento (Acuerdo 017 de 2024) y la división 2007 (JAL 2027).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('territorio_comuna', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->enum('division', ['2024', '2007'])->comment('2024 = Acuerdo 017 (7 comunas + corregimiento); 2007 = división anterior (JAL 2027)');
            $table->string('codigo', 10)->comment('C01…C07, CORR');
            $table->string('nombre', 80);
            $table->enum('tipo', ['comuna', 'corregimiento']);
            $table->string('slug', 80)->nullable();
            $table->decimal('area_ha', 8, 2)->nullable();
            $table->unique(['division', 'codigo'], 'uq_comuna');
        });

        Schema::create('territorio_barrio', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('nombre', 120);
            $table->enum('tipo', ['barrio', 'sector', 'vereda'])->default('barrio');
            $table->unsignedSmallInteger('comuna_2024_id');
            $table->unsignedSmallInteger('comuna_2007_id')->nullable()->comment('Correspondencia con la división anterior');
            $table->string('codigo_externo', 20)->nullable()->comment('Código compartido con el CRM y la Encuesta Itagüí 2026');
            $table->boolean('activo')->default(true);
            $table->unique(['comuna_2024_id', 'nombre'], 'uq_barrio');
            $table->foreign('comuna_2024_id', 'fk_barrio_c24')->references('id')->on('territorio_comuna');
            $table->foreign('comuna_2007_id', 'fk_barrio_c07')->references('id')->on('territorio_comuna');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('territorio_barrio');
        Schema::dropIfExists('territorio_comuna');
    }
};
