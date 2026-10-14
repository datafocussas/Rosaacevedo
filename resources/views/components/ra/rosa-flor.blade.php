@props(['etiqueta' => null])
{{-- Flor sola de la rosa v3: tamaños menores de 96 px y marcadores de imagen (diseño v2, §9.2). --}}
@php $uid = 'flor-'.\Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(6)); @endphp
<svg {{ $attributes->merge(['class' => 'ra-rosa-flor']) }} viewBox="98 50 204 204" @if ($etiqueta) role="img" aria-label="{{ $etiqueta }}" @else aria-hidden="true" focusable="false" @endif>
<defs>
<linearGradient id="{{ $uid }}-p" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ff9a7f"/><stop offset="0.55" stop-color="#f4583f"/><stop offset="1" stop-color="#a82a18"/></linearGradient>
<linearGradient id="{{ $uid }}-q" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#e2492f"/><stop offset="1" stop-color="#7e1d10"/></linearGradient>
</defs>
<path d="M200 68 C150 68 102 100 100 150 C102 200 146 238 200 242 C254 238 298 200 300 150 C298 100 250 68 200 68 Z" fill="#9e2615"/>
<path d="M200 70 C156 70 126 100 130 140 C146 118 172 110 200 112 C228 110 254 118 270 140 C274 100 244 70 200 70 Z" fill="url(#{{ $uid }}-q)"/>
<path d="M200 234 C146 232 104 198 100 150 C98 126 108 108 122 98 C120 126 134 164 170 182 C184 190 196 194 200 194 Z" fill="url(#{{ $uid }}-p)"/>
<path d="M200 234 C254 232 296 198 300 150 C302 126 292 108 278 98 C280 126 266 164 230 182 C216 190 204 194 200 194 Z" fill="url(#{{ $uid }}-p)"/>
<path d="M156 140 C156 110 178 94 200 94 C222 94 244 110 244 140 C244 162 224 176 200 176 C176 176 156 162 156 140 Z" fill="url(#{{ $uid }}-q)"/>
<path d="M200 108 C182 108 172 120 176 132 C180 144 202 146 210 136 C218 126 210 116 198 118 C190 120 190 130 198 130" fill="none" stroke="#6e170c" stroke-width="7" stroke-linecap="round"/>
<path d="M200 242 C168 240 142 222 136 194 C158 206 180 210 200 210 C220 210 242 206 264 194 C258 222 232 240 200 242 Z" fill="url(#{{ $uid }}-p)"/>
<path d="M122 98 C120 126 134 164 170 182 M278 98 C280 126 266 164 230 182" fill="none" stroke="#e2c58b" stroke-width="3"/>
</svg>
