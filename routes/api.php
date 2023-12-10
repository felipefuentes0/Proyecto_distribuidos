<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ControladorJuego;

Route::get('jugadores', [ControladorJuego::class, 'listarJugadores']);
Route::post('unirse', [ControladorJuego::class, 'unirseJuego']);
Route::post('jugar', [ControladorJuego::class, 'iniciarJuego']);
Route::post('reiniciar', [ControladorJuego::class, 'reiniciarJuego']);
Route::post('limpiar', [ControladorJuego::class, 'limpiarAlmacenamiento']);
Route::get('palabra-generada', [ControladorJuego::class, 'mostrarPalabraGenerada']);

