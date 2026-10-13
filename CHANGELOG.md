# Changelog

Todos los cambios notables de este proyecto se documentan en este archivo.

El formato sigue las recomendaciones de [Keep a Changelog](https://keepachangelog.com/es/1.0.0/)
y el proyecto adhiere a [Semantic Versioning](https://semver.org/lang/es/).

## [1.0.4] - 2026-10-13

### Agregado
- **Panel de administración para cerrar sesiones abiertas**: nueva página en `Administración del sitio → Plugins → Plugins locales → Cerrar sesiones SENCE abiertas` que permite al administrador:
  - Ver todas las sesiones abiertas (inicios sin cierre posterior)
  - Filtrar por antigüedad mínima (evitar cerrar sesiones en curso)
  - Filtrar por curso (opcional)
  - Cerrar masivamente con un solo clic
  - Registrar los cierres con la acción `cierre_administrativo` (diferenciado de `cierre_automatico`)
- Nueva cadena de idioma `cierre_administrativo` para distinguir cierres realizados por el administrador.

### Corregido

#### DV "0" en el RUN del alumno
- La función `local_senceasistencia_get_run_alumno()` descartaba el dígito verificador "0" por usar `empty()`. En PHP, `empty("0")` devuelve `true`, haciendo que el DV se perdiera.
- Ahora usa comparación estricta `!== ''` que respeta el "0" como valor válido.
- Se agregó validación de longitud del DV (debe tener exactamente 1 carácter).
- Se agregó `trim()` para eliminar espacios accidentales en los campos `rut` y `dv`.
- Se mejoró el manejo de excepciones con logging en modo debug.

#### Cierre de sesión con `sence_idsession` distinto al inicio
- SENCE puede generar un `sence_idsession` **nuevo** en cada operación. La lógica anterior buscaba el cierre por coincidencia exacta de `sence_idsession`, por lo que nunca encontraba el inicio correspondiente.
- Ahora se busca el **último inicio sin cierre posterior** por combinación de `userid + courseid + ambiente + timecreated`.
- En `retoma.php`, si SENCE no envía `IdSesionSence` en el cierre, se recupera del inicio abierto.
- En `hook_callbacks.php`, la detección de sesión activa usa la misma lógica.

### Notas
- El DV "0" es un valor válido según el algoritmo del RUN chileno.
- El core de Moodle no muestra el "0" en el perfil del usuario (bug del core), pero el plugin lo lee correctamente desde la base de datos.
- Los cierres guardados como `error_301` previos a esta versión no se modifican (histórico preservado).
- El panel de administración está disponible solo para usuarios con capability `moodle/site:config`.

### Bugs conocidos
- Ninguno pendiente por el momento.

---

### Corregido
- Se corrige problema de DV en 0 para envio al SENCE
- Se corrige bug de cierre de Sesion SENCE
- Se corrige redacción de README.md


## [1.0.3] - 2026-10-05

### Agregado
- Cronómetro de sesión visible en el bloque SENCE
- Alerta visual a los 10 minutos restantes
- Alerta de expiración al llegar a 0
- Columna `duracion` en la tabla `local_sence_asistencia`
- Configuración de duración máxima de sesión (minutos)
- Soporte para cierre de sesión con tokens de un solo uso

### Corregido
- Detección de inicio/cierre usando el token en lugar de `sence_idsession`
- Recuperación de datos del inicio cuando SENCE no los envía en el cierre
- Manejo del error 301 como cierre válido cuando corresponde

### Bugs conocidos
- **DV "0"**: cuando el dígito verificador es `0`, la función `local_senceasistencia_get_run_alumno()` lo descarta por usar `empty()`. Fix planificado para v1.0.4.
- **Cierre con diferente `sence_idsession`**: SENCE puede generar un `sence_idsession` nuevo en cada operación, causando que el cierre no se asocie al inicio. Fix planificado para v1.0.5.

## [1.0.2] - 2026-09-30

### Agregado
- Tabla `local_sence_tokens` para retorno sin cookies cross-origin
- Página `error.php` con manejo completo del Anexo 2 de SENCE
- Página `retoma.php` con validación de tokens
- Hook `before_standard_top_of_body_html_generation` para inyectar el bloque

### Corregido
- Pérdida de sesión al volver de SENCE
- Detección de inicio/cierre por `sence_idsession`

## [1.0.1] - 2026-09-27

### Agregado
- Selector de ambiente (Test/Producción) en el panel de administración
- URLs separadas por ambiente
- Función `local_senceasistencia_get_ambiente()`
- Función `local_senceasistencia_crear_token()`

### Corregido
- Instalación de la tabla `local_sence_tokens` (faltaba `upgrade.php`)

## [1.0.0] - 2026-09-26

### Agregado
- Versión inicial del plugin
- Integración con el Registro de Asistencia E-Learning de SENCE
- Botón "Iniciar Sesión SENCE" inyectado en cursos
- Registro de inicio y cierre de sesión en base de datos
- Configuración de RUT OTEC y Token
- Soporte para campos personalizados `rut`, `dv`, `sence_codigo`
- Manejo de errores del Anexo 2 de SENCE