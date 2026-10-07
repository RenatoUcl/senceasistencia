<?php
// Cadenas en español
defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'Registro Asistencia SENCE';
$string['configuracion'] = 'Configuración';

// Configuración
$string['ambiente'] = 'Ambiente';
$string['ambiente_desc'] = 'Seleccione el ambiente de SENCE';
$string['ambiente_selector_desc'] = 'Elija "Test" para ambiente de pruebas o "Producción" para ambiente real.';
$string['ambiente_test'] = 'Test';
$string['ambiente_prod'] = 'Producción';
$string['urls_test_heading'] = 'URLs del ambiente Test';
$string['urls_prod_heading'] = 'URLs del ambiente Producción';
$string['otec_heading'] = 'Datos del OTEC';
$string['campos_heading'] = 'Campos personalizados';
$string['sence_url_iniciar_test'] = 'Test - URL Inicio de Sesión';
$string['sence_url_iniciar_test_desc'] = 'Endpoint de inicio de sesión en Test';
$string['sence_url_cerrar_test'] = 'Test - URL Cierre de Sesión';
$string['sence_url_cerrar_test_desc'] = 'Endpoint de cierre de sesión en Test';
$string['sence_url_iniciar_prod'] = 'Producción - URL Inicio de Sesión';
$string['sence_url_iniciar_prod_desc'] = 'Endpoint de inicio de sesión en Producción';
$string['sence_url_cerrar_prod'] = 'Producción - URL Cierre de Sesión';
$string['sence_url_cerrar_prod_desc'] = 'Endpoint de cierre de sesión en Producción';
$string['sence_rut_otec'] = 'RUT del OTEC';
$string['sence_rut_otec_desc'] = 'RUT del OTEC sin puntos y con guión. Formato: xxxxxxxx-x';
$string['sence_token'] = 'Token SENCE';
$string['sence_token_desc'] = 'Token generado en https://sistemas.sence.cl/rts';
$string['sence_linea_default'] = 'Línea de capacitación por defecto';
$string['sence_linea_default_desc'] = 'Línea que se usará si el curso no tiene una configurada';
$string['sence_campo_curso'] = 'Campo personalizado de curso';
$string['sence_campo_curso_desc'] = 'Nombre corto del campo personalizado del curso que guarda el código SENCE';
$string['sence_campo_rut'] = 'Campo personalizado de usuario - RUT';
$string['sence_campo_rut_desc'] = 'Nombre corto del campo personalizado del usuario que guarda el cuerpo del RUT (sin dígito verificador)';
$string['sence_campo_dv'] = 'Campo personalizado de usuario - DV';
$string['sence_campo_dv_desc'] = 'Nombre corto del campo personalizado del usuario que guarda el dígito verificador';

// Mensajes al usuario
$string['iniciarsesion'] = 'Iniciar sesión en SENCE';
$string['cerrarsesion'] = 'Cerrar sesión en SENCE';
$string['ir_sence'] = 'Ir a SENCE para iniciar sesión';
$string['ir_sence_cerrar'] = 'Ir a SENCE para cerrar sesión';
$string['inicio_exitoso'] = 'Inicio de sesión registrado exitosamente en SENCE';
$string['cierre_exitoso'] = 'Cierre de sesión registrado exitosamente en SENCE';
$string['error_sence'] = 'Error al registrar en SENCE';
$string['sin_codigo_sence'] = 'Este curso no tiene configurado un Código SENCE.';
$string['sin_run_alumno'] = 'Su RUT no está configurado. Contacte al administrador.';
$string['instrucciones_claveunica'] = 'Será redirigido a SENCE para autenticarse con su RUT y Clave Única.';
$string['claveunica_link'] = 'Obtener Clave Única aquí';
$string['volver_al_curso'] = 'Volver al curso';
$string['volver_intentar'] = 'Intentar nuevamente';
$string['registro_exitoso'] = 'Registro exitoso';
$string['detalle_error'] = 'Detalle del error';
$string['codigo_error'] = 'Código de error';
$string['sence_session_activa'] = 'Tiene una sesión activa en SENCE. Recuerde cerrarla al terminar.';
$string['sence_session_cerrada'] = 'Su sesión en SENCE ha sido cerrada.';
$string['config_incompleta'] = 'La configuración del plugin SENCE está incompleta. Contacte al administrador.';

// Duración de sesión
$string['duracion_heading'] = 'Duración de la sesión';
$string['duracion_heading_desc'] = 'Configure el tiempo máximo que puede durar una sesión de SENCE antes de alertar al alumno.';
$string['duracion_sesion'] = 'Duración máxima de sesión (minutos)';
$string['duracion_sesion_desc'] = 'Tiempo en minutos que dura una sesión en el curso. Se recomienda que coincida con la duración de la sesión informada en SENCE.';

// Cronómetro
$string['cronometro_titulo'] = 'Tiempo de sesión restante';
$string['cronometro_expirado'] = '¡Tiempo de sesión agotado!';
$string['cronometro_alerta'] = '¡Quedan menos de 10 minutos de sesión!';
$string['cronometro_cerrar_ahora'] = 'Cerrar sesión ahora';
$string['cronometro_renovar'] = 'Renovar sesión';
$string['cronometro_nota'] = 'Recuerde cerrar sesión antes de que termine el tiempo.';