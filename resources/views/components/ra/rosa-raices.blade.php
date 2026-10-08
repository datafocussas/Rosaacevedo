@props(['etiqueta' => 'Rosa plantada con sus raíces en la tierra de Itagüí'])
{{-- Ilustración de marca «Aquí me planto» en línea (componente RosaRaices): la rosa abierta y firme, con
     espinas para defender, hojas que suben y raíces que se aferran a la tierra de Itagüí, con las montañas
     del valle detrás. Propuesta de Tecnología hasta la pieza definitiva de Comunicaciones. --}}
<svg {{ $attributes->merge(['class' => 'ra-rosa']) }} viewBox="0 0 240 380" role="img" aria-label="{{ $etiqueta }}">
<!-- montañas del valle -->
<path class="ra-r-montana" d="M0 252 L38 214 L62 230 L96 196 L130 226 L160 204 L196 232 L222 214 L240 226 L240 252Z"/>
<!-- raíces: se aferran a la tierra -->
<g class="ra-r-raiz">
 <path d="M120 270 C121 300 118 330 112 372" stroke-width="9"/>
 <path d="M119 276 C100 290 80 296 58 312 C44 322 34 338 26 360" stroke-width="7"/>
 <path d="M121 276 C140 290 160 296 182 312 C196 322 206 338 214 360" stroke-width="7"/>
 <path d="M118 290 C104 306 92 326 86 352 C84 362 80 370 74 376" stroke-width="5"/>
 <path d="M122 290 C136 306 148 326 154 352 C156 362 160 370 166 376" stroke-width="5"/>
 <path d="M70 302 C56 300 36 304 14 316" stroke-width="4"/>
 <path d="M170 302 C184 300 204 304 226 316" stroke-width="4"/>
 <path d="M58 312 C50 326 48 340 52 356" stroke-width="3"/>
 <path d="M182 312 C190 326 192 340 188 356" stroke-width="3"/>
 <path d="M114 330 C100 340 96 352 98 366" stroke-width="3"/>
 <path d="M115 345 C124 352 130 362 132 374" stroke-width="2.5"/>
 <path d="M36 338 C26 340 16 348 8 358" stroke-width="2.5"/>
 <path d="M204 338 C214 340 224 348 232 358" stroke-width="2.5"/>
</g>
<!-- tierra: Itagüí -->
<path class="ra-r-tierra" d="M4 254 C40 246 80 250 120 248 C160 246 200 250 236 254 L236 284 C200 288 160 284 120 286 C80 288 40 284 4 288Z"/>
<text class="ra-r-texto" x="120" y="273" text-anchor="middle">ITAGÜÍ</text>
<!-- tallo firme -->
<path class="ra-r-tallo" d="M120 252 C118 220 122 190 119 158 C118 146 120 136 120 128" stroke-width="8"/>
<path class="ra-r-espina" d="M117 226 l-11 -2 l9 -8z"/><path class="ra-r-espina" d="M122 196 l11 -4 l-8 -8z"/><path class="ra-r-espina" d="M118 166 l-10 -3 l8 -7z"/>
<!-- hojas que suben -->
<path class="ra-r-hoja" d="M119 210 C100 186 70 178 44 186 C58 212 92 222 119 210Z"/>
<path class="ra-r-vena" d="M116 208 C96 200 74 194 52 190" stroke-width="2.5"/>
<path class="ra-r-hoja-oscura" d="M121 178 C140 152 170 144 198 150 C186 176 152 188 121 178Z"/>
<path class="ra-r-vena" d="M124 176 C144 168 166 160 190 154" stroke-width="2.5"/>
<!-- cáliz -->
<path class="ra-r-hoja" d="M120 132 C106 140 90 142 76 136 C90 130 104 126 120 122 C136 126 150 130 164 136 C150 142 134 140 120 132Z"/>
<!-- flor: pétalos por capas -->
<path class="ra-r-petalo-sombra" d="M62 72 C56 36 84 12 120 12 C156 12 184 36 178 72 C174 108 150 132 120 134 C90 132 66 108 62 72Z"/>
<path class="ra-r-petalo-int" d="M64 74 C52 42 76 16 106 22 C98 40 92 58 94 82Z"/>
<path class="ra-r-petalo-int" d="M176 74 C188 42 164 16 134 22 C142 40 148 58 146 82Z"/>
<path class="ra-r-petalo" d="M88 38 C94 10 146 10 152 38 C140 30 100 30 88 38Z"/>
<path class="ra-r-petalo-sombra" d="M86 58 C86 34 154 34 154 58 C154 80 138 96 120 96 C102 96 86 80 86 58Z"/>
<path class="ra-r-petalo-int" d="M94 58 C94 36 146 36 146 58 C146 74 134 82 120 82 C106 82 94 74 94 58Z"/>
<path class="ra-r-petalo" d="M94 56 C96 40 114 32 128 36 C112 42 104 54 105 70 C99 68 94 63 94 56Z"/>
<path class="ra-r-petalo" d="M146 52 C144 66 132 76 116 75 C128 68 135 58 134 44 C141 44 146 47 146 52Z"/>
<path class="ra-r-petalo-int" d="M60 68 C54 100 78 124 110 128 C92 114 84 94 88 70 C80 62 66 60 60 68Z"/>
<path class="ra-r-petalo-int" d="M180 68 C186 100 162 124 130 128 C148 114 156 94 152 70 C160 62 174 60 180 68Z"/>
<path class="ra-r-petalo" d="M70 74 C70 104 90 124 114 130 C104 116 96 100 96 84 C88 76 78 72 70 74Z"/>
<path class="ra-r-petalo" d="M170 74 C170 104 150 124 126 130 C136 116 144 100 144 84 C152 76 162 72 170 74Z"/>
<path class="ra-r-petalo" d="M86 86 C98 100 110 106 120 106 C130 106 142 100 154 86 C152 112 138 130 120 132 C102 130 88 112 86 86Z"/>
<path class="ra-r-brillo" fill="none" stroke-linecap="round" stroke-width="3" d="M90 90 C100 100 110 104 120 104 C130 104 140 100 150 90"/>
<path class="ra-r-brillo" fill="none" stroke-linecap="round" stroke-width="2.5" d="M74 80 C74 96 82 110 94 120"/>
<path class="ra-r-brillo" fill="none" stroke-linecap="round" stroke-width="2.5" d="M166 80 C166 96 158 110 146 120"/>
</svg>
