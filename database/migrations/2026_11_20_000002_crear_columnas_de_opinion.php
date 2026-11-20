<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

// Columnas de opinión: columnistas (con foto y perfil) y sus columnas, con el mismo flujo editorial de las noticias.
// En un sitio que ya tiene menú, agrega «Opinión» al pie; en una instalación nueva lo pone MenuSeeder.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('columnistas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre', 120);
            $table->string('slug', 140)->unique('uq_columnista_slug');
            $table->string('perfil', 160)->nullable()->comment('Una línea: «Lideresa comunal de San Pío X»');
            $table->text('biografia')->nullable();
            $table->boolean('activo')->default(true);
            $table->smallInteger('orden')->default(0);
            $table->timestamps();
        });

        Schema::create('columnas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('columnista_id');
            $table->string('slug', 160)->unique('uq_columna_slug');
            $table->string('titulo', 120);
            $table->string('resumen', 300)->nullable();
            $table->mediumText('cuerpo')->nullable();
            $table->enum('estado', ['borrador', 'revision', 'programada', 'publicada', 'archivada'])->default('borrador');
            $table->dateTime('publicada_en')->nullable();
            $table->string('seo_titulo', 70)->nullable();
            $table->string('seo_descripcion', 160)->nullable();
            $table->timestamps();
            $table->index(['estado', 'publicada_en'], 'ix_columna_pub');
            $table->foreign('columnista_id', 'fk_columna_columnista')->references('id')->on('columnistas');
        });

        if (DB::table('menu_items')->exists() && ! DB::table('menu_items')->where('url', '/opinion')->exists()) {
            DB::table('menu_items')->insert([
                'ubicacion' => 'pie_sitio', 'texto' => 'Opinión', 'url' => '/opinion', 'activo' => true,
                'orden' => (int) DB::table('menu_items')->where('ubicacion', 'pie_sitio')->max('orden') + 1,
            ]);
        }
    }

    public function down(): void
    {
        DB::table('menu_items')->where('url', '/opinion')->delete();
        Schema::dropIfExists('columnas');
        Schema::dropIfExists('columnistas');
    }
};
