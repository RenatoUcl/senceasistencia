<?php
// English strings
defined('MOODLE_INTERNAL') || die();

$string['pluginname'] = 'SENCE Attendance Registry';
$string['configuracion'] = 'Settings';

// Settings
$string['ambiente'] = 'Environment';
$string['ambiente_desc'] = 'Select SENCE environment';
$string['ambiente_selector_desc'] = 'Choose "Test" for testing environment or "Production" for real environment.';
$string['ambiente_test'] = 'Test';
$string['ambiente_prod'] = 'Production';
$string['urls_test_heading'] = 'Test environment URLs';
$string['urls_prod_heading'] = 'Production environment URLs';
$string['otec_heading'] = 'OTEC data';
$string['campos_heading'] = 'Custom fields';
$string['sence_url_iniciar_test'] = 'Test - Login URL';
$string['sence_url_iniciar_test_desc'] = 'Test endpoint for login';
$string['sence_url_cerrar_test'] = 'Test - Logout URL';
$string['sence_url_cerrar_test_desc'] = 'Test endpoint for logout';
$string['sence_url_iniciar_prod'] = 'Production - Login URL';
$string['sence_url_iniciar_prod_desc'] = 'Production endpoint for login';
$string['sence_url_cerrar_prod'] = 'Production - Logout URL';
$string['sence_url_cerrar_prod_desc'] = 'Production endpoint for logout';
$string['sence_rut_otec'] = 'OTEC RUT';
$string['sence_rut_otec_desc'] = 'OTEC RUT without dots and with hyphen. Format: xxxxxxxx-x';
$string['sence_token'] = 'SENCE Token';
$string['sence_token_desc'] = 'Token generated at https://sistemas.sence.cl/rts';
$string['sence_linea_default'] = 'Default training line';
$string['sence_linea_default_desc'] = 'Training line if the course has none configured';
$string['sence_campo_curso'] = 'Course custom field';
$string['sence_campo_curso_desc'] = 'Shortname of the course custom field storing the SENCE code';
$string['sence_campo_rut'] = 'User custom field - RUT';
$string['sence_campo_rut_desc'] = 'Shortname of the user custom field storing the RUT body (without check digit)';
$string['sence_campo_dv'] = 'User custom field - DV';
$string['sence_campo_dv_desc'] = 'Shortname of the user custom field storing the check digit';

// User messages
$string['iniciarsesion'] = 'Log in to SENCE';
$string['cerrarsesion'] = 'Log out from SENCE';
$string['ir_sence'] = 'Go to SENCE to log in';
$string['ir_sence_cerrar'] = 'Go to SENCE to log out';
$string['inicio_exitoso'] = 'Login successfully registered in SENCE';
$string['cierre_exitoso'] = 'Logout successfully registered in SENCE';
$string['error_sence'] = 'Error registering in SENCE';
$string['sin_codigo_sence'] = 'This course does not have a SENCE Code configured.';
$string['sin_run_alumno'] = 'Your RUT is not configured. Contact the administrator.';
$string['instrucciones_claveunica'] = 'You will be redirected to SENCE to authenticate with your RUT and Clave Única.';
$string['claveunica_link'] = 'Get Clave Única here';
$string['volver_al_curso'] = 'Back to course';
$string['volver_intentar'] = 'Try again';
$string['registro_exitoso'] = 'Registration successful';
$string['detalle_error'] = 'Error detail';
$string['codigo_error'] = 'Error code';
$string['sence_session_activa'] = 'You have an active SENCE session. Remember to log out when done.';
$string['sence_session_cerrada'] = 'Your SENCE session has been closed.';
$string['config_incompleta'] = 'SENCE plugin configuration is incomplete. Contact the administrator.';

// Session duration
$string['duracion_heading'] = 'Session duration';
$string['duracion_heading_desc'] = 'Configure the maximum time a SENCE session can last before alerting the student.';
$string['duracion_sesion'] = 'Maximum session duration (minutes)';
$string['duracion_sesion_desc'] = 'Time in minutes a session lasts in the course. Recommended to match the session duration informed to SENCE.';

// Countdown timer
$string['cronometro_titulo'] = 'Remaining session time';
$string['cronometro_expirado'] = 'Session time expired!';
$string['cronometro_alerta'] = 'Less than 10 minutes left in your session!';
$string['cronometro_cerrar_ahora'] = 'Log out now';
$string['cronometro_renovar'] = 'Renew session';
$string['cronometro_nota'] = 'Remember to log out before time runs out.';