<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Asistente virtual del sitio: base de conocimiento que carga el equipo y conversaciones anónimas (sin nombre ni
// celular; la IP solo como huella HMAC) que se borran solas después de los días de retención del panel.
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistente_conocimiento', function (Blueprint $table) {
            $table->increments('id');
            $table->string('titulo', 160);
            $table->string('categoria', 40)->default('otro');
            $table->mediumText('contenido');
            $table->string('fuente', 255)->nullable()->comment('De dónde sale el dato: «Hoja de vida oficial, oct 2026»');
            $table->boolean('activo')->default(true);
            $table->smallInteger('orden')->default(0);
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->index(['activo', 'categoria', 'orden'], 'ix_conocimiento_activo');
        });

        Schema::create('asistente_conversaciones', function (Blueprint $table) {
            $table->increments('id');
            $table->uuid('uuid')->unique('uq_conversacion_uuid');
            $table->char('ip_hash', 64)->nullable();
            $table->string('pagina', 255)->nullable()->comment('Ruta desde la que se abrió');
            $table->unsignedSmallInteger('mensajes')->default(0)->comment('Preguntas de la persona');
            $table->unsignedInteger('tokens_entrada')->default(0);
            $table->unsignedInteger('tokens_salida')->default(0);
            $table->timestamps();
            $table->index('created_at', 'ix_conversacion_fecha');
        });

        Schema::create('asistente_mensajes', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('conversacion_id');
            $table->enum('rol', ['usuario', 'asistente']);
            $table->text('contenido');
            $table->string('estado', 20)->default('ok')->comment('ok | error | limite | rechazo');
            $table->timestamp('created_at')->nullable();
            $table->index(['conversacion_id', 'id'], 'ix_mensaje_conversacion');
            $table->foreign('conversacion_id', 'fk_mensaje_conversacion')->references('id')->on('asistente_conversaciones')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistente_mensajes');
        Schema::dropIfExists('asistente_conversaciones');
        Schema::dropIfExists('asistente_conocimiento');
    }
};
