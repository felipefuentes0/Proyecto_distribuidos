<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ControladorJuego extends Controller
{
    private $rutaInformacion;


    public function __construct()
    {
        $this->rutaInformacion = storage_path('datos_juego');
    }

    private function obtenerArchivoInformacion($nombreArchivo)
    {
        return $this->rutaInformacion . $nombreArchivo . '.json';
    }

    private function leerInformacion($nombreArchivo, $valorPredeterminado = [])
    {
        $archivo = $this->obtenerArchivoInformacion($nombreArchivo);

        if (file_exists($archivo)) {
            $contenido = file_get_contents($archivo);
            return json_decode($contenido, true);
        }

        return $valorPredeterminado;
    }

    private function escribirInformacion($nombreArchivo, $datos)
    {
        $archivo = $this->obtenerArchivoInformacion($nombreArchivo);
        $contenido = json_encode($datos, JSON_PRETTY_PRINT);
        file_put_contents($archivo, $contenido);
    }

    public function listarJugadores()
    {
        $jugadoresEnEspera = $this->leerInformacion('jugadores_en_espera', []);
        return response()->json(['Jugadores' => $jugadoresEnEspera], 200);
    }

    public function unirseJuego(Request $solicitud)
    {
        $nombre = $solicitud->input('nombre');
        $jugadoresEnEspera = $this->leerInformacion('jugadores_en_espera', []);
        $conteoTurnos = $this->leerInformacion('conteo_turnos', 1);

        if (count($jugadoresEnEspera) < 3) {
            $turno = $conteoTurnos;
            $jugadoresEnEspera[] = ['nombre' => $nombre, 'turno' => $turno, 'intentos' => 2];
            $conteoTurnos++;
            $this->escribirInformacion('jugadores_en_espera', $jugadoresEnEspera);
            $this->escribirInformacion('conteo_turnos', $conteoTurnos);
            $jugador = ['nombre' => $nombre, 'turno' => $turno];

      
            if (count($jugadoresEnEspera) === 3) {
                $this->generarPalabraAleatoria();
            }

            return response()->json(['mensaje' => 'Jugador añadido', 'info' => $jugador], 200);
        } else {
            return response()->json(['mensaje' => 'Sala Llena'], 200);
        }
    }

    private function generarPalabraAleatoria()
    {
        $palabras = [
            'gato', 'perro', 'sol', 'luna', 'casa', 'auto', 'mesa', 'flor', 'mar', 'pato',
            'pez', 'sol', 'luz', 'mano', 'pie', 'piedra', 'papel', 'tijera', 'rojo', 'azul',
            'verde', 'amarillo', 'nube', 'hoja', 'nieve', 'agua', 'fuego', 'viento', 'tierra',
            'rayo', 'rayo', 'taza', 'cuchara', 'plato', 'silla', 'mesa', 'puerta', 'ventana'];
        $palabraAleatoria = $palabras[array_rand($palabras)];
        $this->escribirInformacion('palabra_aleatoria', $palabraAleatoria);
        return response()->json(['mensaje' => 'Palabra aleatoria generada', 'Palabra' => $palabraAleatoria], 200);
    }

    public function limpiarAlmacenamiento()
    {
        $this->escribirInformacion('jugadores_en_espera', []);
        $this->escribirInformacion('conteo_turnos', 1);

        return response()->json(['mensaje' => 'Datos eliminados'], 200);
    }

    public function iniciarJuego(Request $solicitud)
    {
        $jugadoresEnEspera = $this->leerInformacion('jugadores_en_espera', []);
        $palabra = $this->leerInformacion('palabra_aleatoria', '');

        if (count($jugadoresEnEspera) == 3 || $palabra != '') {
            $turnoActual = $this->leerInformacion('turno_actual', 1);

            if ($turnoActual > count($jugadoresEnEspera)) {
                $turnoActual = 1;
            }

            $nombre = $solicitud->input('nombre');

            if ($this->esTurnoJugador($jugadoresEnEspera, $turnoActual, $nombre)) {
                $progresoJugador = $this->leerInformacion("progreso_jugador_$nombre", []);

                $esCorrecta = $this->verificarSiLetraEsCorrecta($palabra, $solicitud->input('letra'));
                $progresoJugador = $this->actualizarProgresoJugador($progresoJugador, $solicitud->input('letra'), $esCorrecta, $nombre);
                
                if (!$esCorrecta) {
       
                    $intentosRestantes = $this->decrementarIntentos($jugadoresEnEspera, $nombre);

                  
                    if ($intentosRestantes === 0) {
                        $this->eliminarJugador($jugadoresEnEspera, $nombre);
                        $this->reasignarTurnos($jugadoresEnEspera);
                    }
                }

                $turnoActual++;
                $this->escribirInformacion('turno_actual', $turnoActual);

                if ($esCorrecta) {
                    $mensaje = "Letra jugador $nombre, turno " . ($turnoActual - 1) . " es " . strtoupper($solicitud->input('letra'));
                    $mensaje .= ", Acertó";
                    $mensaje .= ", Progreso " . implode('', $progresoJugador);

                    if (!in_array('*', $progresoJugador)) {
                        $this->reiniciarJuego(); 
                        return response()->json(['mensaje' => $mensaje, 'estado_juego' => 'Juego terminado, palabra adivinada'], 200);
                    }

                    return response()->json(['mensaje' => $mensaje], 200);
                } else {
                    return response()->json(['mensaje' => "Letra jugador $nombre, turno " . ($turnoActual - 1) . " es " . strtoupper($solicitud->input('letra')) . ", Falló. Intentos restantes: $intentosRestantes"], 200);
                }
            } else {
                return response()->json(['error' => 'No es tu turno o el nombre es incorrecto. Letra rechazada o alguien adivinó la palabra.'], 400);
            }
        } else {
            return response()->json(['mensaje' => 'No se puede iniciar el juego. Jugadores insuficientes.'], 200);
        }
    }

    public function reiniciarJuego()
    {
        $this->escribirInformacion('jugadores_en_espera', []);
        $this->escribirInformacion('conteo_turnos', 1);
        $this->escribirInformacion('turno_actual', 1);
        $this->escribirInformacion('palabra_aleatoria', '');

        return response()->json(['mensaje' => 'Juego reiniciado.'], 200);
    }

    private function actualizarProgresoJugador($progresoJugador, $letra, $esCorrecta, $nombreJugador)
    {
        if ($esCorrecta) {
            $palabra = $this->leerInformacion('palabra_aleatoria', '');
            $progresoActualizado = [];

            foreach (str_split($palabra) as $indice => $caracter) {
                if (strtoupper($caracter) === strtoupper($letra) || (isset($progresoJugador[$indice]) && $progresoJugador[$indice] !== '*')) {
                    $progresoActualizado[] = strtoupper($caracter);
                } else {
                    $progresoActualizado[] = '*';
                }
            }

            foreach ($progresoActualizado as $indice => $caracter) {
                $progresoJugador[$indice] = $caracter;
            }

            $this->escribirInformacion("progreso_jugador_$nombreJugador", $progresoJugador);
        }

        return $progresoJugador;
    }

    private function esTurnoJugador($jugadoresEnEspera, $turnoActual, $nombre)
    {
        foreach ($jugadoresEnEspera as $jugador) {
            if ($jugador['nombre'] === $nombre && $jugador['turno'] === $turnoActual) {
                return true;
            }
        }

        return false;
    }

    private function verificarSiLetraEsCorrecta($palabra, $letra)
    {
        return stripos($palabra, $letra) !== false;
    }

    private function incrementarIntentos(&$jugadoresEnEspera, $nombre)
    {
        foreach ($jugadoresEnEspera as &$jugador) {
            if ($jugador['nombre'] === $nombre) {
                $jugador['intentos']++;
                break;
            }
        }
    }

    private function superarMaximoIntentos($jugadoresEnEspera, $nombre)
    {
        foreach ($jugadoresEnEspera as $jugador) {
            if ($jugador['nombre'] === $nombre && $jugador['intentos'] >= 2) {
                return true;
            }
        }

        return false;
    }

    private function eliminarJugador(&$jugadoresEnEspera, $nombre)
    {
        foreach ($jugadoresEnEspera as $indice => $jugador) {
            if ($jugador['nombre'] === $nombre) {
                unset($jugadoresEnEspera[$indice]);
                break;
            }
        }
    }

    private function reasignarTurnos(&$jugadoresEnEspera)
    {
        $turno = 1;

        foreach ($jugadoresEnEspera as &$jugador) {
            $jugador['turno'] = $turno;
            $turno++;
        }

        $this->escribirInformacion('jugadores_en_espera', $jugadoresEnEspera);
    }

    private function decrementarIntentos(&$jugadoresEnEspera, $nombre)
    {
        foreach ($jugadoresEnEspera as &$jugador) {
            if ($jugador['nombre'] === $nombre) {
                $jugador['intentos'] = max(0, $jugador['intentos'] - 1);
                return $jugador['intentos'];
            }
        }

        return 0;
    }

    public function mostrarPalabraGenerada()
    {
        $palabraGenerada = $this->leerInformacion('palabra_aleatoria', '');

        if (!empty($palabraGenerada)) {
            return response()->json(['mensaje' => 'Palabra generada', 'Palabra' => $palabraGenerada], 200);
        } else {
            return response()->json(['mensaje' => 'No hay palabra generada'], 404);
        }
    }


}
