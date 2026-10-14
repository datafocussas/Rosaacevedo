<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Ciudadanía. Un ciudadano = un registro, deduplicado por el HMAC del celular (nunca en texto plano).
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('politicas', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->enum('tipo', ['tratamiento_datos', 'autorizacion_general', 'whatsapp', 'afinidad_politica', 'publicar_propuesta', 'cookies', 'uso_ia']);
            $table->string('version', 10);
            $table->mediumText('texto');
            $table->char('hash_sha256', 64)->comment('Huella del texto exacto aceptado');
            $table->dateTime('vigente_desde');
            $table->boolean('vigente')->default(false);
            $table->unique(['tipo', 'version'], 'uq_politica');
        });

        Schema::create('ciudadanos', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique('uq_ciudadano_uuid');
            $table->char('celular_hash', 64)->unique('uq_ciudadano_celular')->comment('HMAC-SHA256 del celular E.164: llave de deduplicación');
            $table->binary('celular_cifrado', length: 255)->comment('Cifrado con APP_KEY (cast encrypted de Laravel)');
            $table->string('nombre', 120);
            $table->string('email', 160)->nullable();
            $table->unsignedSmallInteger('barrio_id')->nullable()->index('ix_ciudadano_barrio');
            $table->unsignedTinyInteger('paso_alcanzado')->default(1);
            $table->boolean('es_voluntario')->default(false);
            $table->json('primer_origen')->nullable()->comment('utm_*, codigo_q, pagina, variante del primer contacto');
            $table->enum('estado', ['activo', 'inactivo', 'retirado'])->default('activo')->comment('retirado = revocó autorización');
            $table->string('crm_id', 64)->nullable();
            $table->enum('crm_sync_estado', ['pendiente', 'sincronizado', 'error'])->default('pendiente')->index('ix_ciudadano_sync');
            $table->dateTime('crm_sync_en')->nullable();
            $table->timestamps();
            $table->foreign('barrio_id', 'fk_ciudadano_barrio')->references('id')->on('territorio_barrio');
        });

        Schema::create('consentimientos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ciudadano_id');
            $table->unsignedSmallInteger('politica_id')->comment('Tipo y versión exacta del texto mostrado');
            $table->boolean('otorgado')->comment('1 = otorga, 0 = revoca');
            $table->string('formulario', 40)->comment('registro_p1, buzon, evento, qr, titular…');
            $table->binary('ip', length: 16)->comment('inet_pton');
            $table->string('user_agent', 255)->nullable();
            $table->string('url', 255)->nullable();
            $table->timestamp('created_at', 3)->useCurrent();
            $table->index(['ciudadano_id', 'politica_id', 'created_at'], 'ix_consent_ciudadano');
            $table->foreign('ciudadano_id', 'fk_consent_ciudadano')->references('id')->on('ciudadanos');
            $table->foreign('politica_id', 'fk_consent_politica')->references('id')->on('politicas');
            $table->comment('Solo inserción: nunca UPDATE ni DELETE. El estado vigente es el último registro por política.');
        });

        Schema::create('voluntariado', function (Blueprint $table) {
            $table->unsignedBigInteger('ciudadano_id')->primary();
            $table->json('intereses')->nullable()->comment('vecinos, redes, eventos, testigo, transporte…');
            $table->json('disponibilidad')->nullable()->comment('dias y franjas');
            $table->string('puesto_votacion', 160)->nullable()->comment('Solo si la persona lo comparte');
            $table->timestamp('updated_at')->nullable();
            $table->foreign('ciudadano_id', 'fk_vol_ciudadano')->references('id')->on('ciudadanos')->cascadeOnDelete();
        });

        Schema::create('enlaces_cortos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('codigo', 20)->unique('uq_codigo');
            $table->string('destino', 255);
            $table->string('pieza', 120)->nullable()->comment('Volante, valla, video, post…');
            $table->unsignedSmallInteger('comuna_id')->nullable();
            $table->unsignedInteger('evento_id')->nullable();
            $table->unsignedInteger('clics')->default(0);
            $table->boolean('activo')->default(true);
            $table->timestamp('created_at')->nullable();
            $table->foreign('comuna_id', 'fk_ec_comuna')->references('id')->on('territorio_comuna');
            $table->foreign('evento_id', 'fk_ec_evento')->references('id')->on('eventos');
        });

        Schema::create('interacciones', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ciudadano_id')->nullable()->index('ix_int_ciudadano');
            $table->char('visitante_id', 36)->nullable()->comment('Cookie de primera parte para A/B');
            $table->string('tipo', 40)->comment('registro_paso_1/2/3, propuesta, asistencia, whatsapp_clic, qr…');
            $table->string('variante', 10)->nullable();
            $table->string('pagina', 255)->nullable();
            $table->unsignedSmallInteger('comuna_pagina_id')->nullable();
            $table->string('utm_source', 80)->nullable();
            $table->string('utm_medium', 80)->nullable();
            $table->string('utm_campaign', 120)->nullable();
            $table->string('utm_content', 120)->nullable();
            $table->string('codigo_q', 20)->nullable();
            $table->string('referrer', 255)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index(['tipo', 'created_at'], 'ix_int_tipo_fecha');
            $table->index(['variante', 'tipo'], 'ix_int_variante');
            $table->foreign('ciudadano_id', 'fk_int_ciudadano')->references('id')->on('ciudadanos');
        });

        Schema::create('temas', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('nombre', 60);
            $table->unsignedSmallInteger('eje_id')->nullable();
            $table->smallInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->foreign('eje_id', 'fk_tema_eje')->references('id')->on('ejes');
        });

        Schema::create('propuestas_ciudadanas', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 16)->unique('uq_prop_codigo')->comment('PR-AAAA-NNNN');
            $table->unsignedBigInteger('ciudadano_id');
            $table->unsignedSmallInteger('tema_id')->index('ix_prop_tema')->comment('Tema elegido por el ciudadano');
            $table->unsignedSmallInteger('tema_confirmado_id')->nullable()->comment('Tema confirmado por el moderador');
            $table->unsignedSmallInteger('barrio_id')->nullable();
            $table->text('texto');
            $table->boolean('publicar_anonima')->default(false);
            $table->enum('estado', ['recibida', 'en_revision', 'incorporada', 'respondida', 'descartada'])->default('recibida');
            $table->json('sugerencia_ia')->nullable()->comment('tema, comuna, sentimiento, resumen, modelo, fecha');
            $table->unsignedBigInteger('asignada_a')->nullable();
            $table->text('nota_interna')->nullable();
            $table->text('respuesta')->nullable();
            $table->boolean('destacada')->default(false);
            $table->timestamps();
            $table->index(['estado', 'created_at'], 'ix_prop_estado');
            $table->foreign('ciudadano_id', 'fk_prop_ciudadano')->references('id')->on('ciudadanos');
            $table->foreign('tema_id', 'fk_prop_tema')->references('id')->on('temas');
            $table->foreign('tema_confirmado_id', 'fk_prop_tema_conf')->references('id')->on('temas');
            $table->foreign('barrio_id', 'fk_prop_barrio')->references('id')->on('territorio_barrio');
        });

        Schema::create('asistencias_evento', function (Blueprint $table) {
            $table->unsignedInteger('evento_id');
            $table->unsignedBigInteger('ciudadano_id');
            $table->enum('estado', ['inscrito', 'asistio', 'cancelo'])->default('inscrito');
            $table->timestamps();
            $table->primary(['evento_id', 'ciudadano_id']);
            $table->foreign('evento_id', 'fk_asis_evento')->references('id')->on('eventos');
            $table->foreign('ciudadano_id', 'fk_asis_ciudadano')->references('id')->on('ciudadanos');
        });

        Schema::create('solicitudes_titular', function (Blueprint $table) {
            $table->increments('id');
            $table->string('radicado', 16)->unique('uq_radicado');
            $table->unsignedBigInteger('ciudadano_id')->nullable();
            $table->enum('tipo', ['consulta', 'actualizacion', 'supresion', 'revocatoria', 'reclamo']);
            $table->string('nombre', 120);
            $table->string('contacto', 160);
            $table->text('detalle')->nullable();
            $table->date('vence_en')->comment('10 días hábiles consultas; 15 reclamos');
            $table->enum('estado', ['abierta', 'en_tramite', 'respondida', 'cerrada'])->default('abierta');
            $table->text('respuesta')->nullable();
            $table->unsignedBigInteger('atendida_por')->nullable();
            $table->timestamps();
            $table->index(['estado', 'vence_en'], 'ix_sol_vence');
            $table->foreign('ciudadano_id', 'fk_sol_ciudadano')->references('id')->on('ciudadanos');
        });

        Schema::create('crm_outbox', function (Blueprint $table) {
            $table->id();
            $table->string('entidad', 40)->comment('ciudadano, consentimiento, propuesta, asistencia, interaccion');
            $table->unsignedBigInteger('entidad_id');
            $table->string('evento', 40)->comment('creado, actualizado, revocado');
            $table->json('payload');
            $table->char('idempotencia', 36)->unique('uq_idem');
            $table->unsignedTinyInteger('intentos')->default(0);
            $table->enum('estado', ['pendiente', 'enviado', 'error'])->default('pendiente');
            $table->string('ultimo_error', 500)->nullable();
            $table->dateTime('proximo_intento')->nullable();
            $table->timestamps();
            $table->index(['estado', 'proximo_intento'], 'ix_outbox');
        });
    }

    public function down(): void
    {
        foreach (['crm_outbox', 'solicitudes_titular', 'asistencias_evento', 'propuestas_ciudadanas', 'temas', 'interacciones', 'enlaces_cortos', 'voluntariado', 'consentimientos', 'ciudadanos', 'politicas'] as $tabla) {
            Schema::dropIfExists($tabla);
        }
    }
};
