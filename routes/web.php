<?php

use App\Http\Controllers\AgendaController;
use App\Http\Controllers\BuzonController;
use App\Http\Controllers\ComunasController;
use App\Http\Controllers\DosFactoresController;
use App\Http\Controllers\EnlaceCortoController;
use App\Http\Controllers\NoticiasController;
use App\Http\Controllers\PropuestasController;
use App\Http\Controllers\RegistroController;
use App\Http\Controllers\SitioController;
use App\Http\Controllers\TitularController;
use App\Models\Pagina;
use Illuminate\Support\Facades\Route;

// Sitio público. Orden: variante A/B → origen → CSP → caché de respuestas completa.
Route::middleware(['variante', 'origen', 'csp', 'cache.respuesta'])->group(function () {
    Route::get('/', [SitioController::class, 'inicio'])->name('inicio');
    Route::get('/conoce-a-rosa', [SitioController::class, 'pagina'])->defaults('slug', 'conoce-a-rosa')->name('conoce-a-rosa');
    Route::get('/manifiesto', [SitioController::class, 'pagina'])->defaults('slug', 'manifiesto')->name('manifiesto');
    Route::get('/uso-de-ia', [SitioController::class, 'pagina'])->defaults('slug', 'uso-de-ia')->name('uso-de-ia');
    Route::get('/politica-de-datos', [SitioController::class, 'politicaDatos'])->name('politica-de-datos');
    Route::get('/transparencia', [SitioController::class, 'transparencia'])->name('transparencia');
    Route::get('/enlaces', [SitioController::class, 'enlaces'])->name('enlaces');

    Route::get('/propuestas', [PropuestasController::class, 'index'])->name('propuestas');
    Route::get('/propuestas/{eje}', [PropuestasController::class, 'eje'])->name('propuestas.eje');

    Route::get('/comunas', [ComunasController::class, 'index'])->name('comunas');
    Route::get('/comunas/{comuna}', [ComunasController::class, 'show'])->name('comunas.show');

    Route::get('/noticias', [NoticiasController::class, 'index'])->name('noticias');
    Route::get('/noticias/{noticia}', [NoticiasController::class, 'show'])->name('noticias.show');

    Route::get('/agenda', [AgendaController::class, 'index'])->name('agenda');
    Route::get('/agenda/{evento}', [AgendaController::class, 'show'])->name('agenda.show');
    Route::get('/agenda/{evento}/evento.ics', [AgendaController::class, 'ics'])->name('agenda.ics');

    Route::get('/buzon', [BuzonController::class, 'index'])->name('buzon');
    Route::get('/sumate', [RegistroController::class, 'index'])->name('sumate');

    Route::get('/robots.txt', [SitioController::class, 'robots']);
    Route::get('/sitemap.xml', [SitioController::class, 'sitemap']);

    // Páginas creadas en el panel: rosaacevedo.com/{ruta}. Va de última y no toma rutas reservadas.
    Route::get('/{slug}', [SitioController::class, 'pagina'])->name('pagina')
        ->where('slug', '(?!(?:'.implode('|', Pagina::RESERVADAS).'|inicio)$)[a-z0-9]+(?:-[a-z0-9]+)*');
});

// Formularios HTML clásicos (funcionan sin JavaScript) y páginas que no se cachean.
Route::middleware(['variante', 'origen', 'csp', 'throttle:envios'])->group(function () {
    Route::post('/sumate', [RegistroController::class, 'store'])->name('sumate.store');
    Route::post('/sumate/continuar', [RegistroController::class, 'completar'])->name('sumate.completar');
    Route::post('/buzon', [BuzonController::class, 'store'])->name('buzon.store');
    Route::post('/agenda/{evento}/asistencia', [AgendaController::class, 'asistencia'])->name('agenda.asistencia');
    Route::post('/mis-datos', [TitularController::class, 'store'])->name('mis-datos.store');
});

Route::middleware(['variante', 'origen', 'csp'])->group(function () {
    Route::get('/sumate/continuar', [RegistroController::class, 'continuar'])->name('sumate.continuar');
    Route::get('/sumate/gracias', [RegistroController::class, 'gracias'])->name('sumate.gracias');
    Route::get('/buzon/gracias', [BuzonController::class, 'gracias'])->name('buzon.gracias');
    Route::get('/mis-datos', [TitularController::class, 'index'])->name('mis-datos');
    Route::get('/q/{codigo}', EnlaceCortoController::class)->name('q')->where('codigo', '[A-Za-z0-9_-]+');
});

// Vista previa con enlace temporal firmado (flujo editorial).
Route::get('/vista-previa/noticias/{noticia:id}', [NoticiasController::class, 'vistaPrevia'])->name('vista-previa.noticia')->middleware('signed');
Route::get('/vista-previa/paginas/{pagina:id}', [SitioController::class, 'vistaPrevia'])->name('vista-previa.pagina')->middleware('signed');

// Doble factor del panel.
Route::middleware(['web', 'auth'])->prefix('admin/seguridad')->name('dos-factores.')->group(function () {
    Route::get('/doble-factor', [DosFactoresController::class, 'mostrar'])->name('mostrar');
    Route::post('/doble-factor', [DosFactoresController::class, 'verificar'])->name('verificar')->middleware('throttle:5,1');
});
