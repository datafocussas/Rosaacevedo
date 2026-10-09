{{-- Trayectoria (diseño v2, §7): línea oro de 1 px, puntos esmeralda. Cada periodo y cargo solo con la hoja de vida oficial. --}}
<section class="ra-seccion {{ $clasesFondo }}" aria-labelledby="trayectoria-{{ $loop->index ?? 0 }}">
    <div class="ra-contenedor">
        <div class="ra-lectura-centrada ra-pila-5">
            <span class="ra-etiqueta">{{ $d['etiqueta'] ?? 'Raíces' }}</span>
            <h2 class="ra-h2-medio" id="trayectoria-{{ $loop->index ?? 0 }}">{{ $d['titulo'] ?? 'Trayectoria' }}</h2>
            <ol class="ra-linea-tiempo">
                @foreach ($d['items'] ?? [] as $item)
                    <li data-aparecer>
                        <span class="ra-etiqueta">{!! \App\Support\Texto::marcarPendientes(e($item['periodo'] ?? '')) !!}</span>
                        <h3 class="ra-h3">{!! \App\Support\Texto::marcarPendientes(e($item['titulo'] ?? '')) !!}</h3>
                        @if (! empty($item['texto']))<p class="ra-sin-margen ra-texto-secundario">{!! \App\Support\Texto::marcarPendientes(e($item['texto'])) !!}</p>@endif
                    </li>
                @endforeach
            </ol>
        </div>
    </div>
</section>
