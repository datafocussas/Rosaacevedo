<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

// Doble factor obligatorio, último acceso y bloqueo tras 5 intentos fallidos (RNF-05, sección 03).
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->text('dos_factores_secreto')->nullable()->after('password');
            $table->timestamp('dos_factores_confirmado_en')->nullable()->after('dos_factores_secreto');
            $table->timestamp('ultimo_acceso_en')->nullable();
            $table->unsignedTinyInteger('intentos_fallidos')->default(0);
            $table->timestamp('bloqueado_hasta')->nullable();
            $table->boolean('activo')->default(true);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['dos_factores_secreto', 'dos_factores_confirmado_en', 'ultimo_acceso_en', 'intentos_fallidos', 'bloqueado_hasta', 'activo']);
        });
    }
};
