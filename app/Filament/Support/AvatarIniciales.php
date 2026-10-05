<?php

namespace App\Filament\Support;

use Filament\AvatarProviders\Contracts\AvatarProvider;
use Illuminate\Database\Eloquent\Model;

/** Avatar con iniciales en SVG local: el panel no consulta servicios externos. */
class AvatarIniciales implements AvatarProvider
{
    public function get(Model $record): string
    {
        $iniciales = collect(explode(' ', (string) $record->name))->filter()->take(2)->map(fn ($p) => mb_strtoupper(mb_substr($p, 0, 1)))->join('');
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 64 64"><rect width="64" height="64" fill="#003c57"/><text x="32" y="40" font-family="Montserrat,Arial,sans-serif" font-size="24" font-weight="700" fill="#f8f3ef" text-anchor="middle">'.e($iniciales).'</text></svg>';

        return 'data:image/svg+xml;base64,'.base64_encode($svg);
    }
}
