<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Contenido administrable. Las imágenes viven en `media` (spatie/laravel-medialibrary).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ejes', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('slug', 80)->unique('uq_eje_slug');
            $table->string('articulo', 20)->comment('la, las, los, una ciudad que');
            $table->string('sujeto', 60);
            $table->string('icono', 40)->comment('Nombre Lucide');
            $table->string('frase', 200)->nullable();
            $table->json('contenido')->nullable()->comment('Bloques del Builder');
            $table->json('compromisos')->nullable();
            $table->smallInteger('orden')->default(0);
            $table->boolean('publicado')->default(false);
            $table->timestamps();
        });

        Schema::create('paginas', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug', 120)->unique('uq_pagina_slug');
            $table->string('titulo', 160);
            $table->json('bloques')->nullable();
            $table->string('seo_titulo', 70)->nullable();
            $table->string('seo_descripcion', 160)->nullable();
            $table->enum('estado', ['borrador', 'publicada', 'archivada'])->default('borrador');
            $table->timestamps();
        });

        Schema::create('comuna_paginas', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->unsignedSmallInteger('comuna_id')->unique('uq_comuna_pagina');
            $table->text('saludo')->nullable();
            $table->json('bloques')->nullable();
            $table->string('codigo_whatsapp', 30)->nullable();
            $table->boolean('publicada')->default(false);
            $table->timestamp('updated_at')->nullable();
            $table->foreign('comuna_id', 'fk_cp_comuna')->references('id')->on('territorio_comuna');
        });

        Schema::create('banners', function (Blueprint $table) {
            $table->increments('id');
            $table->string('pagina', 40)->default('inicio')->comment('inicio | comuna');
            $table->unsignedSmallInteger('comuna_id')->nullable();
            $table->string('etiqueta', 60)->nullable();
            $table->string('titular', 70);
            $table->string('texto', 160)->nullable();
            $table->boolean('mostrar_lema')->default(false);
            $table->string('btn1_texto', 30)->nullable();
            $table->string('btn1_url', 255)->nullable();
            $table->string('btn2_texto', 30)->nullable();
            $table->string('btn2_url', 255)->nullable();
            $table->string('alt', 200)->comment('Texto alternativo obligatorio; imágenes en media (colecciones escritorio y movil)');
            $table->char('variante', 1)->nullable()->comment('A, B… para prueba A/B');
            $table->smallInteger('orden')->default(0);
            $table->dateTime('publicar_desde')->nullable();
            $table->dateTime('publicar_hasta')->nullable();
            $table->enum('estado', ['borrador', 'activo', 'archivado'])->default('borrador');
            $table->timestamps();
            $table->index(['pagina', 'comuna_id', 'estado', 'orden'], 'ix_banner_vigente');
            $table->foreign('comuna_id', 'fk_banner_comuna')->references('id')->on('territorio_comuna');
        });

        Schema::create('noticias', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug', 160)->unique('uq_noticia_slug');
            $table->string('titulo', 90);
            $table->string('resumen', 300)->nullable();
            $table->mediumText('cuerpo')->nullable();
            $table->unsignedSmallInteger('eje_id')->nullable();
            $table->unsignedBigInteger('autor_id')->nullable();
            $table->enum('estado', ['borrador', 'revision', 'programada', 'publicada', 'archivada'])->default('borrador');
            $table->dateTime('publicada_en')->nullable();
            $table->string('video_url', 255)->nullable();
            $table->string('seo_titulo', 70)->nullable();
            $table->string('seo_descripcion', 160)->nullable();
            $table->timestamps();
            $table->index(['estado', 'publicada_en'], 'ix_noticia_pub');
            $table->foreign('eje_id', 'fk_noticia_eje')->references('id')->on('ejes');
        });

        Schema::create('noticia_comuna', function (Blueprint $table) {
            $table->unsignedInteger('noticia_id');
            $table->unsignedSmallInteger('comuna_id');
            $table->primary(['noticia_id', 'comuna_id']);
            $table->foreign('noticia_id', 'fk_nc_noticia')->references('id')->on('noticias')->cascadeOnDelete();
            $table->foreign('comuna_id', 'fk_nc_comuna')->references('id')->on('territorio_comuna');
        });

        Schema::create('eventos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug', 160)->unique('uq_evento_slug');
            $table->string('titulo', 120);
            $table->text('descripcion')->nullable();
            $table->enum('tipo', ['encuentro', 'recorrido', 'foro', 'reunion', 'otro'])->default('encuentro');
            $table->dateTime('inicia_en');
            $table->dateTime('termina_en')->nullable();
            $table->string('lugar', 160)->nullable();
            $table->string('direccion', 200)->nullable();
            $table->decimal('lat', 9, 6)->nullable();
            $table->decimal('lng', 9, 6)->nullable();
            $table->unsignedSmallInteger('comuna_id')->nullable();
            $table->unsignedInteger('cupo')->nullable();
            $table->boolean('publico')->default(true);
            $table->enum('estado', ['borrador', 'publicado', 'cancelado', 'realizado'])->default('borrador');
            $table->timestamps();
            $table->index(['estado', 'inicia_en'], 'ix_evento_fecha');
            $table->foreign('comuna_id', 'fk_evento_comuna')->references('id')->on('territorio_comuna');
        });

        Schema::create('enlaces_bio', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('texto', 60);
            $table->string('url', 255);
            $table->string('icono', 40)->nullable();
            $table->smallInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
        });
    }

    public function down(): void
    {
        foreach (['enlaces_bio', 'eventos', 'noticia_comuna', 'noticias', 'banners', 'comuna_paginas', 'paginas', 'ejes'] as $tabla) {
            Schema::dropIfExists($tabla);
        }
    }
};
