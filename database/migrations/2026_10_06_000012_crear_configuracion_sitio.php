<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ajustes', function (Blueprint $table) {
            $table->string('clave', 80)->primary();
            $table->json('valor')->nullable();
            $table->string('grupo', 40)->default('general');
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_at')->nullable();
        });

        Schema::create('menu_items', function (Blueprint $table) {
            $table->increments('id');
            $table->enum('ubicacion', ['principal', 'pie_sitio', 'pie_transparencia']);
            $table->string('texto', 60);
            $table->string('url', 255);
            $table->smallInteger('orden')->default(0);
            $table->boolean('activo')->default(true);
            $table->index(['ubicacion', 'orden'], 'ix_menu');
        });

        Schema::create('redes_sociales', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('nombre', 40);
            $table->string('url', 255);
            $table->string('icono', 40);
            $table->smallInteger('orden')->default(0);
            $table->boolean('activa')->default(true);
        });

        Schema::create('redirecciones', function (Blueprint $table) {
            $table->increments('id');
            $table->string('desde', 255)->unique('uq_desde');
            $table->string('hacia', 255);
            $table->smallInteger('codigo')->default(301);
            $table->unsignedInteger('visitas')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('redirecciones');
        Schema::dropIfExists('redes_sociales');
        Schema::dropIfExists('menu_items');
        Schema::dropIfExists('ajustes');
    }
};
