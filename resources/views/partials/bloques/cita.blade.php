{{-- Cita (diseño v2, §7): abismo (cita grande en marfil, autor en rótulo oro) o marfil (cita en noche, comillas en coral hondo). --}}
<section class="ra-seccion {{ $clasesFondo }}">
    <div class="ra-contenedor">
        <blockquote class="ra-cita ra-lectura-centrada" data-aparecer>
            <p><span class="ra-comillas">“</span>{!! \App\Support\Texto::marcarPendientes(e(trim($d['texto'] ?? '', " \"“”"))) !!}<span class="ra-comillas">”</span></p>
            @if (! empty($d['autor']))<footer>{{ $d['autor'] }}</footer>@endif
        </blockquote>
    </div>
</section>
