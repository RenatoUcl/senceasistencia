# Plugin Moodle: Integración Registro Asistencia SENCE

Plugin de tipo `local` para Moodle 4.4+ que integra el **Registro de Asistencia E-Learning del SENCE** (Servicio Nacional de Capacitación y Empleo de Chile) con la plataforma Moodle del OTEC.

## Descripción

Este plugin implementa la **integración por redirección** descrita en el Manual Técnico v1.1.6 de SENCE. Permite a los participantes de cursos E-Learning registrar su conectividad mediante autenticación con Clave Única.

### Características

- ✅ Selector de ambiente (Test / Producción) desde el panel de administración
- ✅ Botón "Iniciar Sesión SENCE" inyectado automáticamente en cursos
- ✅ Autenticación de participantes con Clave Única
- ✅ Registro de inicio y cierre de sesión en base de datos de Moodle
- ✅ Cronómetro de sesión con alerta a los 10 minutos
- ✅ Manejo de errores del Anexo 2 del manual SENCE
- ✅ Soporte para campos personalizados de usuario (rut + dv separados)
- ✅ Tokens de retorno para evitar pérdida de sesión cross-origin
- ✅ Compatible con Franquicia Tributaria, Programas Sociales y FPT

## Requisitos

-------------------------------------------------------------
| Requisito         | Versión mínima                        |
|-------------------|---------------------------------------|
| Moodle            | 4.4 o superior                        |
| PHP               | 8.0 o superior                        |
| MySQL / MariaDB   | 5.7 / 10.4 o superior                 |
| Extensión cURL    | Habilitada                            |
| HTTPS             | Obligatorio (SENCE no acepta HTTP)    |
-------------------------------------------------------------

## Instalación

### 1. Descargar el plugin

* git clone https://github.com/RenatoUcl/senceasistencia.git
* o descargar el ZIP y descomprimirlo en /moodle/local/senceasistencia

### 2. Asignar permisos 

sudo chown -R www-data:www-data /ruta/a/moodle/local/senceasistencia
sudo chmod -R 755 /ruta/a/moodle/local/senceasistencia

### 3. Instalar en Moodle 

    1. Ir a Administración del sitio -> Notificiaciones
    2. Hacer clic en "Actualizar base de datos de Moodle"
    3. Esperar a terminar la instalación

### 4. Configurar el Plugin 

Ir a "Administración del sitio" ->  "Extensiones" -> "Extensiones Locales" -> "Registro Asistencia SENCE" y completar lo siguiente 
(puede que en la pantalla de instalacion le pida completar estos datos, segun la versión de Moodle)

    Campo                       : Valor
    Ambiente                    : Test (para pruebas) o Produccion (Campo seleccionable)
    URL TEST
        Iniciar                 : https://sistemas.sence.cl/rcetest/Registro/IniciarSesion
        Cerrar                  : https://sistemas.sence.cl/rcetest/Registro/CerrarSesion
    URL PRODUCCIÓN
        Iniciar                 : https://sistemas.sence.cl/rce/Registro/IniciarSesion
        Cerrar                  : https://sistemas.sence.cl/rce/Registro/CerrarSesion
    DATOS OTEC
        RUT OTEC                : RUT (sin puntos, con guión. ej: 12345678-9)
        TOKEN SENCE             : Token generado en https://sistemas.sence.cl/rts (Se debe contar con la clave del administrador de la OTEC y/o responsable para genar el token y se pueden generar N Tokens)
        Linea de Capacitación   : 1 [Programas Sociales], 3 [Franquicia Tribuaria], 6 [FPT]
    CAMPOS PERSONALIZADOS
        Campo personalizado de curso        : sence_codigo
        Campo personalizado de usuario RUT  : rut (Rut sin puntos, sin guión y sin digito verificador)
        Campo personalizado de usuario DV   : dv (Digito verificador de rut)
    DURACIÓN DE LA SESIÓN
        Duración máxima de sesion en minutos    : 240

### 5. Preparación de los cursos

Cada curso que use integración SENCE debe tener:
* Un campo personalizado llamado sence_codigo (o el que hayas configurado)
* El Código SENCE real del curso en ese campo
* Un nombre corto (shortname) que corresponda al CodigoCurso de SENCE 

** Nota: El CodigoCurso, viene generado tambien por sence, y este debe estar en las Ordenes de compra y/o en el detalle que genera la OTIC/SENCE con el listado de participantes. los participantes deben coincidir con el codigo del curso y codigo Sence

#### 6. Preparar los usuarios

Cada alumno debe tener:

* Un campo personalizado llamado rut con el cuerpo del RUT (sin puntos ni guión)
* Un campo personalizado llamado dv con el dígito verificador
* Clave Única activa en https://claveunica.gob.cl/

### Uso

# Para el alumno

    Entrar al curso en Moodle
    Ver el bloque "Registro Asistencia SENCE" al inicio del contenido
    Hacer clic en "Ir a SENCE para iniciar sesión"
    Autenticarse con RUT + Clave Única
    Volver al curso (automáticamente)
    Ver el cronómetro con el tiempo restante
    Opcional: al terminar, hacer clic en "Ir a SENCE para cerrar sesión"

# Para el administrador

    Verificar que los alumnos estén autorizados en SENCE para el curso
    Consultar los registros en la tabla mdl_local_sence_asistencia
    Revisar los logs de error en caso de problemas

# Base de datos

El plugin crea 2 tablas:

    - local_sence_asistencia, Registra inicio y cierre de sesión de los alumnos.

    Columna                 Tipo            Descripción
    id                      int             ID autogenerado
    courseid	            int	            ID del curso en Moodle
    userid	                int	            ID del usuario en Moodle
    sence_codsence	        char(10)        Código SENCE del curso
    sence_idsession	        char(149)   	ID de sesión devuelto por SENCE
    sence_idsessionalumno	char(149)	    ID de sesión local (MDL-XXX-...)
    runalumno	            char(12)	    RUN del alumno (xxxxxxxx-x)
    accion	                char(20)	    inicio, cierre, cierre_automatico, error_XXX
    glosaerror	            int	            Código de error de SENCE (si aplica)
    ambiente	            char(10)	    test o prod
    fechahora	            char(19)	    Fecha/hora devuelta por SENCE
    zonahoraria	            char(100)	    Zona horaria de SENCE
    timecreated	            int	            Timestamp de creación
    duracion	            int	            Duración de la sesión en segundos

    - local_sence_tokens, Tokens de un solo uso para retorno desde SENCE (sin cookies cross-origin).

    Columna	                Tipo        	Descripción
    id	                    int	            ID autogenerado
    token	                char(64)	    Token único (hash)
    userid	                int	            ID del usuario
    courseid	            int	            ID del curso
    idsesionalumno	        char(149)	    ID de sesión local
    accion	                char(20)	    inicio o cierre
    timecreated	            int	            Timestamp de creación
    timeexpires	            int	            Timestamp de expiración (30 min)


# Errores comunes de SENCE

Ver el Anexo 2 del Manual Técnico v1.1.6. Los más comunes:

    Código	Descripción	Solución
    200	    Parámetro faltante o mal escrito	Verificar POST en F12
    204	    Código SENCE inválido	Verificar campo sence_codigo del curso
    205	    Código Curso inválido	Verificar shortname del curso
    207	    RUN con formato incorrecto	Verificar campos rut y dv
    208	    RUN no autorizado	Contactar a SENCE para inscribir al alumno
    300	    Error interno de SENCE	Contactar a SENCE
    301	    No se pudo registrar inicio/cierre	Verificar sesión en SENCE
    311	    RUN no coincide con Clave Única	Alumno debe usar su RUT real

# Registro de cambios

Ver CHANGELOG.md.

## Bugs conocidos

#	Bug	Estado
* 1	DV "0" no se envía correctamente	🔴 Pendiente fix v1.0.4
* 2	Cierre con sence_idsession distinto al inicio	🟡 Pendiente fix v1.0.5

# Licencia
GPL v3 - Ver LICENSE.

# Autor

* Nombre: Renato Andrés Soto Moya
* Email: renato.soto@uchile.cl
* Institución: UNIVERSIDAD DE CHILE - FACULTAD DE CIENCIAS SOCIALES

# Soporte

Para reportar bugs o sugerencias, abrir un issue en:
https://github.com/RenatoUCL/moodle-local_senceasistencia/issues

# Recursos oficiales de SENCE

* Manual Técnico v1.1.6           https://sence.gob.cl/sites/default/files/integracion_registro_asistencia_sence_v1.1.6.pdf
* Portal de tokens SENCE          https://sistemas.sence.cl/rts
* Clave Única                     https://claveunica.gob.cl/
* Contacto técnico E-Learning     Control e-learning <controlelearning@sence.cl>