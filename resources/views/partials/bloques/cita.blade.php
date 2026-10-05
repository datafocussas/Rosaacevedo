<section class="ra-seccion-compacta">
    <div class="ra-contenedor ra-lectura">
        <blockquote class="ra-cita">
            <p>{!! \App\Support\Texto::marcarPendientes(e($d['texto'] ?? '')) !!}</p>
            @if (! empty($d['autor']))<footer>{{ $d['autor'] }}</footer>@endif
        </blockquote>
    </div>
</section>
