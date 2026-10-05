{{-- Trayectoria. Cada periodo y cargo solo con la hoja de vida oficial (regla 1). --}}
<section class="ra-seccion ra-franja-raiz" aria-labelledby="trayectoria-{{ $loop->index ?? 0 }}">
    <div class="ra-contenedor ra-lectura ra-pila-4">
        <span class="ra-etiqueta">{{ $d['etiqueta'] ?? 'Raíces' }}</span>
        <h2 class="ra-h2" id="trayectoria-{{ $loop->index ?? 0 }}">{{ $d['titulo'] ?? 'Trayectoria' }}</h2>
        <ol class="ra-linea-tiempo">
            @foreach ($d['items'] ?? [] as $item)
                <li>
                    <span class="ra-etiqueta">{!! \App\Support\Texto::marcarPendientes(e($item['periodo'] ?? '')) !!}</span>
                    <h3 class="ra-h3">{!! \App\Support\Texto::marcarPendientes(e($item['titulo'] ?? '')) !!}</h3>
                    @if (! empty($item['texto']))<p class="ra-sin-margen">{!! \App\Support\Texto::marcarPendientes(e($item['texto'])) !!}</p>@endif
                </li>
            @endforeach
        </ol>
    </div>
</section>
