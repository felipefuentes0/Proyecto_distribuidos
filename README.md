Juegazo API
Esta API proporciona un juego simple de adivinanza de palabras para tres jugadores. A continuación, se detallan las rutas disponibles y cómo interactuar con ellas.

URL Base: http://localhost:8000/api

Rutas
1. Listar Jugadores
   Ruta: GET /jugadores
   Descripción: Obtiene la lista de jugadores en espera.
   Ejemplo de Uso: http://localhost:8000/api/jugadores
2. Unirse al Juego
   Ruta: POST /unirse
   Descripción: Permite que un jugador se una al juego.
   Parámetros del Cuerpo (JSON):
   {
   "nombre": "nombre_del_jugador"
   }
   Ejemplo de Uso: http://localhost:8000/api/unirse
   {
   "nombre": "jugador1"
   }
3. Iniciar Juego
   Ruta: POST /jugar
   Descripción: Inicia el juego y permite que los jugadores realicen sus turnos.
   Parámetros del Cuerpo (JSON):
   {
   "nombre": "nombre_del_jugador",
   "letra": "letra_adivinada"
   }
   Ejemplo de Uso: http://localhost:8000/api/jugar
   {
   "nombre": "jugador1",
   "letra": "a"
   }
4. Reiniciar Juego
   Ruta: POST /reiniciar
   Descripción: Reinicia el juego, eliminando la lista de jugadores en espera y restableciendo los turnos.
   Ejemplo de Uso: http://localhost:8000/api/reiniciar
5. Limpiar Almacenamiento
   Ruta: POST /limpiar
   Descripción: Elimina todos los datos almacenados del juego.
   Ejemplo de Uso: http://localhost:8000/api/limpiar
6. Mostrar Palabra Generada
   Ruta: GET /palabra-generada
   Descripción: Muestra la palabra aleatoria generada para el juego.
   Ejemplo de Uso: http://localhost:8000/api/palabra-generada
   Ejemplo de Flujo del Juego
   Unirse al Juego: Envía una solicitud POST a http://localhost:8000/api/unirse con el nombre del jugador.
   {
   "nombre": "jugador1"
   }
   Mostrar Palabra Generada: Después de que tres jugadores se hayan unido, puedes obtener la palabra aleatoria generada para el juego.
   http://localhost:8000/api/palabra-generada
   Iniciar Juego: Inicia el juego enviando solicitudes POST a http://localhost:8000/api/jugar con el nombre del jugador y la letra adivinada.
   {
   "nombre": "jugador1",
   "letra": "a"
   }
   Repite el paso 3 para cada jugador en su turno hasta que se adivine la palabra o se alcance el límite de intentos.

   Reiniciar Juego: Si se desea reiniciar el juego, puedes enviar una solicitud POST a http://localhost:8000/api/reiniciar.
   http://localhost:8000/api/reiniciar
   Limpiar Almacenamiento: Para eliminar todos los datos del juego, puedes enviar una solicitud POST a http://localhost:8000/api/limpiar.
   http://localhost:8000/api/limpiar
