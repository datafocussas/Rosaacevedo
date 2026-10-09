@props(['etiqueta' => 'Rosa coral con raíces profundas que se anclan en la roca de Itagüí', 'halo' => true, 'decorativa' => false])
{{-- Rosa v3 «Raíz de poder» (diseño v2, §9). Los degradados llevan un prefijo único por instancia porque la
     rosa puede aparecer dos veces en la misma página. Sin halo sobre fondos claros o fotos. --}}
@php $uid = 'rosa-'.\Illuminate\Support\Str::lower(\Illuminate\Support\Str::random(6)); @endphp
<svg {{ $attributes->merge(['class' => 'ra-rosa']) }} viewBox="0 0 400 760" @if ($decorativa) aria-hidden="true" focusable="false" @else role="img" aria-label="{{ $etiqueta }}" @endif>
@if ($halo)
<defs>
<radialGradient id="{{ $uid }}-rr-glow" cx="50%" cy="22%" r="45%"><stop offset="0" stop-color="#ff6b4e" stop-opacity="0.35"></stop><stop offset="1" stop-color="#ff6b4e" stop-opacity="0"></stop></radialGradient>
<linearGradient id="{{ $uid }}-rr-pet" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ff9a7f"></stop><stop offset="0.55" stop-color="#f4583f"></stop><stop offset="1" stop-color="#a82a18"></stop></linearGradient>
<linearGradient id="{{ $uid }}-rr-pet2" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#e2492f"></stop><stop offset="1" stop-color="#7e1d10"></stop></linearGradient>
<linearGradient id="{{ $uid }}-rr-tallo" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#0b4434"></stop><stop offset="0.5" stop-color="#1f8a63"></stop><stop offset="1" stop-color="#0b4434"></stop></linearGradient>
<linearGradient id="{{ $uid }}-rr-hoja" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#2fae7d"></stop><stop offset="1" stop-color="#0b4434"></stop></linearGradient>
<linearGradient id="{{ $uid }}-rr-raiz" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#1f8a63"></stop><stop offset="0.55" stop-color="#c9a86a"></stop><stop offset="1" stop-color="#e2c58b"></stop></linearGradient>
<linearGradient id="{{ $uid }}-rr-roca" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#14445a"></stop><stop offset="1" stop-color="#082233"></stop></linearGradient>
</defs>
<ellipse cx="200" cy="165" rx="185" ry="150" fill="url(#{{ $uid }}-rr-glow)"></ellipse>
<path d="M60 476 L340 476 L372 540 L28 540 Z" fill="url(#{{ $uid }}-rr-roca)"></path>
<path d="M28 548 L372 548 L396 640 L4 640 Z" fill="#0b2b3b"></path>
<path d="M4 648 L396 648 L400 760 L0 760 Z" fill="#072130"></path>
<path d="M40 470 H360" stroke="#c9a86a" stroke-width="1.5"></path>
<path d="M28 544 H372 M4 644 H396" stroke="#c9a86a" stroke-opacity="0.35" stroke-width="1"></path>
<text x="200" y="519" text-anchor="middle" font-family="Montserrat, sans-serif" font-weight="700" font-size="19" letter-spacing="10" fill="#e2c58b">ITAGÜÍ</text>
<g fill="none" stroke="url(#{{ $uid }}-rr-raiz)" stroke-linecap="round">
<path d="M200 488 C170 500 120 506 76 534 C58 546 44 560 34 580" stroke-width="7"></path>
<path d="M200 488 C230 500 280 506 324 534 C342 546 356 560 366 580" stroke-width="7"></path>
<path d="M200 540 C176 556 140 576 112 612 C98 630 90 648 86 672" stroke-width="5"></path>
<path d="M200 540 C224 556 260 576 288 612 C302 630 310 648 314 672" stroke-width="5"></path>
<path d="M199 600 C186 622 168 652 160 700" stroke-width="4"></path>
<path d="M201 600 C214 622 232 652 240 700" stroke-width="4"></path>
<path d="M120 512 C110 530 106 548 108 566" stroke-width="2.5"></path>
<path d="M280 512 C290 530 294 548 292 566" stroke-width="2.5"></path>
<path d="M140 578 C120 584 96 584 72 596" stroke-width="2.5"></path>
<path d="M260 578 C280 584 304 584 328 596" stroke-width="2.5"></path>
</g>
<path d="M192 470 C193 560 196 660 200 752 C204 660 207 560 208 470 Z" fill="url(#{{ $uid }}-rr-raiz)"></path>
<g fill="#e2c58b"><circle cx="34" cy="580" r="4"></circle><circle cx="366" cy="580" r="4"></circle><circle cx="86" cy="672" r="4"></circle><circle cx="314" cy="672" r="4"></circle><circle cx="160" cy="700" r="4"></circle><circle cx="240" cy="700" r="4"></circle><circle cx="108" cy="566" r="3.5"></circle><circle cx="292" cy="566" r="3.5"></circle></g>
<g fill="none" stroke="#e2c58b" stroke-opacity="0.4"><circle cx="34" cy="580" r="9"></circle><circle cx="366" cy="580" r="9"></circle><circle cx="86" cy="672" r="9"></circle><circle cx="314" cy="672" r="9"></circle><circle cx="160" cy="700" r="9"></circle><circle cx="240" cy="700" r="9"></circle></g>
<path d="M194 228 L206 228 L207 470 L193 470 Z" fill="url(#{{ $uid }}-rr-tallo)"></path>
<path d="M194 300 l-10 -6 l10 -2 Z M206 360 l10 -6 l-10 -2 Z M194 425 l-9 -5 l9 -2 Z" fill="#0b4434"></path>
<path d="M196 392 C160 360 116 356 88 372 C120 402 164 406 196 392 Z" fill="url(#{{ $uid }}-rr-hoja)"></path>
<path d="M194 391 C160 382 126 375 96 372" fill="none" stroke="#e2c58b" stroke-width="1.2" stroke-opacity="0.85"></path>
<path d="M204 320 C238 286 284 278 312 290 C286 324 240 336 204 320 Z" fill="url(#{{ $uid }}-rr-hoja)"></path>
<path d="M206 319 C238 304 272 294 304 291" fill="none" stroke="#e2c58b" stroke-width="1.2" stroke-opacity="0.85"></path>
<path d="M200 234 C186 240 168 240 152 232 C168 224 186 222 200 220 C214 222 232 224 248 232 C232 240 214 240 200 234 Z" fill="#1f8a63"></path>
<path d="M200 68 C150 68 102 100 100 150 C102 200 146 238 200 242 C254 238 298 200 300 150 C298 100 250 68 200 68 Z" fill="#9e2615"></path>
<path d="M200 70 C156 70 126 100 130 140 C146 118 172 110 200 112 C228 110 254 118 270 140 C274 100 244 70 200 70 Z" fill="url(#{{ $uid }}-rr-pet2)"></path>
<path d="M200 234 C146 232 104 198 100 150 C98 126 108 108 122 98 C120 126 134 164 170 182 C184 190 196 194 200 194 Z" fill="url(#{{ $uid }}-rr-pet)"></path>
<path d="M200 234 C254 232 296 198 300 150 C302 126 292 108 278 98 C280 126 266 164 230 182 C216 190 204 194 200 194 Z" fill="url(#{{ $uid }}-rr-pet)"></path>
<path d="M156 140 C156 110 178 94 200 94 C222 94 244 110 244 140 C244 162 224 176 200 176 C176 176 156 162 156 140 Z" fill="url(#{{ $uid }}-rr-pet2)"></path>
<path d="M200 176 C176 174 158 160 156 138 C168 150 184 156 200 156 Z" fill="#ff8f73" fill-opacity="0.9"></path>
<path d="M200 176 C224 174 242 160 244 138 C232 150 216 156 200 156 Z" fill="#ff8f73" fill-opacity="0.9"></path>
<path d="M200 108 C182 108 172 120 176 132 C180 144 202 146 210 136 C218 126 210 116 198 118 C190 120 190 130 198 130" fill="none" stroke="#6e170c" stroke-width="3" stroke-linecap="round"></path>
<path d="M200 242 C168 240 142 222 136 194 C158 206 180 210 200 210 C220 210 242 206 264 194 C258 222 232 240 200 242 Z" fill="url(#{{ $uid }}-rr-pet)"></path>
<path d="M122 98 C120 126 134 164 170 182 M278 98 C280 126 266 164 230 182 M136 194 C158 206 180 210 200 210 C220 210 242 206 264 194 M130 140 C146 118 172 110 200 112 C228 110 254 118 270 140" fill="none" stroke="#e2c58b" stroke-width="1.2" stroke-opacity="0.9"></path>
@else
<defs>
<radialGradient id="{{ $uid }}-rr-glow" cx="50%" cy="22%" r="45%"><stop offset="0" stop-color="#ff6b4e" stop-opacity="0.35"></stop><stop offset="1" stop-color="#ff6b4e" stop-opacity="0"></stop></radialGradient>
<linearGradient id="{{ $uid }}-rr-pet" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#ff9a7f"></stop><stop offset="0.55" stop-color="#f4583f"></stop><stop offset="1" stop-color="#a82a18"></stop></linearGradient>
<linearGradient id="{{ $uid }}-rr-pet2" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#e2492f"></stop><stop offset="1" stop-color="#7e1d10"></stop></linearGradient>
<linearGradient id="{{ $uid }}-rr-tallo" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#0b4434"></stop><stop offset="0.5" stop-color="#1f8a63"></stop><stop offset="1" stop-color="#0b4434"></stop></linearGradient>
<linearGradient id="{{ $uid }}-rr-hoja" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#2fae7d"></stop><stop offset="1" stop-color="#0b4434"></stop></linearGradient>
<linearGradient id="{{ $uid }}-rr-raiz" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#1f8a63"></stop><stop offset="0.55" stop-color="#c9a86a"></stop><stop offset="1" stop-color="#e2c58b"></stop></linearGradient>
<linearGradient id="{{ $uid }}-rr-roca" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#14445a"></stop><stop offset="1" stop-color="#082233"></stop></linearGradient>
</defs>

<path d="M60 476 L340 476 L372 540 L28 540 Z" fill="url(#{{ $uid }}-rr-roca)"></path>
<path d="M28 548 L372 548 L396 640 L4 640 Z" fill="#0b2b3b"></path>
<path d="M4 648 L396 648 L400 760 L0 760 Z" fill="#072130"></path>
<path d="M40 470 H360" stroke="#c9a86a" stroke-width="1.5"></path>
<path d="M28 544 H372 M4 644 H396" stroke="#c9a86a" stroke-opacity="0.35" stroke-width="1"></path>
<text x="200" y="519" text-anchor="middle" font-family="Montserrat, sans-serif" font-weight="700" font-size="19" letter-spacing="10" fill="#e2c58b">ITAGÜÍ</text>
<g fill="none" stroke="url(#{{ $uid }}-rr-raiz)" stroke-linecap="round">
<path d="M200 488 C170 500 120 506 76 534 C58 546 44 560 34 580" stroke-width="7"></path>
<path d="M200 488 C230 500 280 506 324 534 C342 546 356 560 366 580" stroke-width="7"></path>
<path d="M200 540 C176 556 140 576 112 612 C98 630 90 648 86 672" stroke-width="5"></path>
<path d="M200 540 C224 556 260 576 288 612 C302 630 310 648 314 672" stroke-width="5"></path>
<path d="M199 600 C186 622 168 652 160 700" stroke-width="4"></path>
<path d="M201 600 C214 622 232 652 240 700" stroke-width="4"></path>
<path d="M120 512 C110 530 106 548 108 566" stroke-width="2.5"></path>
<path d="M280 512 C290 530 294 548 292 566" stroke-width="2.5"></path>
<path d="M140 578 C120 584 96 584 72 596" stroke-width="2.5"></path>
<path d="M260 578 C280 584 304 584 328 596" stroke-width="2.5"></path>
</g>
<path d="M192 470 C193 560 196 660 200 752 C204 660 207 560 208 470 Z" fill="url(#{{ $uid }}-rr-raiz)"></path>
<g fill="#e2c58b"><circle cx="34" cy="580" r="4"></circle><circle cx="366" cy="580" r="4"></circle><circle cx="86" cy="672" r="4"></circle><circle cx="314" cy="672" r="4"></circle><circle cx="160" cy="700" r="4"></circle><circle cx="240" cy="700" r="4"></circle><circle cx="108" cy="566" r="3.5"></circle><circle cx="292" cy="566" r="3.5"></circle></g>
<g fill="none" stroke="#e2c58b" stroke-opacity="0.4"><circle cx="34" cy="580" r="9"></circle><circle cx="366" cy="580" r="9"></circle><circle cx="86" cy="672" r="9"></circle><circle cx="314" cy="672" r="9"></circle><circle cx="160" cy="700" r="9"></circle><circle cx="240" cy="700" r="9"></circle></g>
<path d="M194 228 L206 228 L207 470 L193 470 Z" fill="url(#{{ $uid }}-rr-tallo)"></path>
<path d="M194 300 l-10 -6 l10 -2 Z M206 360 l10 -6 l-10 -2 Z M194 425 l-9 -5 l9 -2 Z" fill="#0b4434"></path>
<path d="M196 392 C160 360 116 356 88 372 C120 402 164 406 196 392 Z" fill="url(#{{ $uid }}-rr-hoja)"></path>
<path d="M194 391 C160 382 126 375 96 372" fill="none" stroke="#e2c58b" stroke-width="1.2" stroke-opacity="0.85"></path>
<path d="M204 320 C238 286 284 278 312 290 C286 324 240 336 204 320 Z" fill="url(#{{ $uid }}-rr-hoja)"></path>
<path d="M206 319 C238 304 272 294 304 291" fill="none" stroke="#e2c58b" stroke-width="1.2" stroke-opacity="0.85"></path>
<path d="M200 234 C186 240 168 240 152 232 C168 224 186 222 200 220 C214 222 232 224 248 232 C232 240 214 240 200 234 Z" fill="#1f8a63"></path>
<path d="M200 68 C150 68 102 100 100 150 C102 200 146 238 200 242 C254 238 298 200 300 150 C298 100 250 68 200 68 Z" fill="#9e2615"></path>
<path d="M200 70 C156 70 126 100 130 140 C146 118 172 110 200 112 C228 110 254 118 270 140 C274 100 244 70 200 70 Z" fill="url(#{{ $uid }}-rr-pet2)"></path>
<path d="M200 234 C146 232 104 198 100 150 C98 126 108 108 122 98 C120 126 134 164 170 182 C184 190 196 194 200 194 Z" fill="url(#{{ $uid }}-rr-pet)"></path>
<path d="M200 234 C254 232 296 198 300 150 C302 126 292 108 278 98 C280 126 266 164 230 182 C216 190 204 194 200 194 Z" fill="url(#{{ $uid }}-rr-pet)"></path>
<path d="M156 140 C156 110 178 94 200 94 C222 94 244 110 244 140 C244 162 224 176 200 176 C176 176 156 162 156 140 Z" fill="url(#{{ $uid }}-rr-pet2)"></path>
<path d="M200 176 C176 174 158 160 156 138 C168 150 184 156 200 156 Z" fill="#ff8f73" fill-opacity="0.9"></path>
<path d="M200 176 C224 174 242 160 244 138 C232 150 216 156 200 156 Z" fill="#ff8f73" fill-opacity="0.9"></path>
<path d="M200 108 C182 108 172 120 176 132 C180 144 202 146 210 136 C218 126 210 116 198 118 C190 120 190 130 198 130" fill="none" stroke="#6e170c" stroke-width="3" stroke-linecap="round"></path>
<path d="M200 242 C168 240 142 222 136 194 C158 206 180 210 200 210 C220 210 242 206 264 194 C258 222 232 240 200 242 Z" fill="url(#{{ $uid }}-rr-pet)"></path>
<path d="M122 98 C120 126 134 164 170 182 M278 98 C280 126 266 164 230 182 M136 194 C158 206 180 210 200 210 C220 210 242 206 264 194 M130 140 C146 118 172 110 200 112 C228 110 254 118 270 140" fill="none" stroke="#e2c58b" stroke-width="1.2" stroke-opacity="0.9"></path>
@endif
</svg>
