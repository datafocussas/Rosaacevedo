<?php

namespace App\Console\Commands;

use App\Models\ConversacionAsistente;
use App\Services\Asistente\AsistenteConfig;
use Illuminate\Console\Command;

/** Borra las conversaciones del asistente más viejas que los días de retención del panel (Ley 1581: finalidad y temporalidad). */
class DepurarAsistente extends Command
{
    protected $signature = 'asistente:depurar';

    protected $description = 'Borra las conversaciones del asistente que superan los días de retención.';

    public function handle(AsistenteConfig $config): int
    {
        $dias = max(1, (int) $config->get('dias_retencion'));
        $borradas = ConversacionAsistente::query()->where('updated_at', '<', now()->subDays($dias))->delete();

        $this->info("Conversaciones borradas: {$borradas} (más de {$dias} días).");

        return self::SUCCESS;
    }
}
