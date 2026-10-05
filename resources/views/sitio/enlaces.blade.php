{{-- «Link in bio» para Instagram y TikTok (RF-17). --}}
<x-layouts.sitio titulo="Enlaces" descripcion="Súmate, deja tu propuesta y sigue a Rosa Acevedo.">
    <section class="ra-seccion">
        <div class="ra-contenedor ra-enlaces">
            <x-ra.rosa-raices class="ra-rosa-enlaces" />
            <h1 class="ra-h2">Rosa Acevedo</h1>
            <p class="ra-sin-margen">Por el futuro de Itagüí.</p>
            <x-ra.boton :href="route('sumate', ['utm_source' => 'bio', 'utm_medium' => 'enlaces'])" :bloque="true">Súmate</x-ra.boton>
            @foreach ($enlaces as $enlace)
                <a class="ra-btn ra-btn-fantasma ra-btn-bloque" href="{{ $enlace->url }}" @if (\Illuminate\Support\Str::startsWith($enlace->url, 'http') && ! \Illuminate\Support\Str::startsWith($enlace->url, url('/'))) target="_blank" rel="noopener" @endif>
                    @if ($enlace->icono)<x-ra.icono :nombre="$enlace->icono" />@endif {{ $enlace->texto }}
                </a>
            @endforeach
            @php $whatsapp = app(\App\Services\Ajustes::class)->enlaceWhatsapp('BIO'); @endphp
            @if ($whatsapp)
                <a class="ra-btn ra-btn-whatsapp ra-btn-bloque" href="{{ $whatsapp }}" target="_blank" rel="noopener" data-conversion="whatsapp_clic" data-umami-event="whatsapp_clic"><x-ra.icono nombre="whatsapp" /> Escríbenos por WhatsApp</a>
            @endif
        </div>
    </section>
</x-layouts.sitio>
