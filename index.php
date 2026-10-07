<?php
// index.php - Página de respaldo con botones SENCE
// (Se accede desde el menú de administración del curso)
require_once('../../config.php');
require_once($CFG->dirroot . '/local/senceasistencia/locallib.php');

$id = required_param('id', PARAM_INT);
$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);
require_login($course);

$context = context_course::instance($id);
$PAGE->set_url('/local/senceasistencia/index.php', array('id' => $id));
$PAGE->set_context($context);
$PAGE->set_title(get_string('pluginname', 'local_senceasistencia'));
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'local_senceasistencia'));

// Verificar configuración del plugin
if (!local_senceasistencia_config_ok()) {
    echo $OUTPUT->notification(get_string('config_incompleta', 'local_senceasistencia'), 'notifyproblem');
    echo $OUTPUT->footer();
    exit;
}

// Verificar código SENCE del curso
$codigosence = local_senceasistencia_get_codigo_sence($id);
if (empty($codigosence)) {
    echo $OUTPUT->notification(get_string('sin_codigo_sence', 'local_senceasistencia'), 'notifyproblem');
    echo $OUTPUT->footer();
    exit;
}

// Verificar RUN del alumno
$runalumno = local_senceasistencia_get_run_alumno($USER->id);
if (empty($runalumno)) {
    echo $OUTPUT->notification(get_string('sin_run_alumno', 'local_senceasistencia'), 'notifyproblem');
    echo $OUTPUT->footer();
    exit;
}

// Datos de configuración
$rutotec     = get_config('local_senceasistencia', 'rut_otec');
$token       = get_config('local_senceasistencia', 'token');
$url_iniciar = local_senceasistencia_get_url_iniciar();
$url_cerrar  = local_senceasistencia_get_url_cerrar();
$ambiente    = local_senceasistencia_get_ambiente();
$lineacap    = local_senceasistencia_get_linea_default();

// Generar IdSesionAlumno único (una sola "s" en "Sesion")
$idsesionalumno = local_senceasistencia_generar_idsesion_alumno($USER->id);

// URLs de retorno con sesskey
$sesskey   = sesskey();
$urlretoma = $CFG->wwwroot . '/local/senceasistencia/retoma.php?id=' . $id . '&sesskey=' . $sesskey;
$urlerror  = $CFG->wwwroot . '/local/senceasistencia/error.php?id=' . $id . '&sesskey=' . $sesskey;

// Verificar sesión activa
$sesionactiva = $DB->get_record('local_sence_asistencia', array(
    'courseid' => $id,
    'userid'   => $USER->id,
    'accion'   => 'inicio',
    'ambiente' => $ambiente
), '*', IGNORE_MULTIPLE);

$tienecierre = false;
if ($sesionactiva) {
    $tienecierre = $DB->record_exists('local_sence_asistencia', array(
        'courseid'        => $id,
        'userid'          => $USER->id,
        'accion'          => 'cierre',
        'sence_idsession' => $sesionactiva->sence_idsession,
        'ambiente'        => $ambiente
    ));
}

// Colores según ambiente
$color_principal = ($ambiente === 'prod') ? '#0f6cbf' : '#6c757d';

echo html_writer::start_div('', array(
    'style' => 'max-width:600px;margin:20px auto;padding:20px;'
             . 'border:2px solid ' . $color_principal . ';'
             . 'border-radius:8px;background:#f0f8ff;text-align:center;'
));

echo html_writer::tag('p', get_string('instrucciones_claveunica', 'local_senceasistencia'));

echo html_writer::tag('p', html_writer::link(
    'https://claveunica.gob.cl/',
    get_string('claveunica_link', 'local_senceasistencia'),
    array('target' => '_blank')
));

// ==================== BOTÓN INICIO ====================
if (!$sesionactiva || $tienecierre) {
    echo html_writer::start_tag('form', array(
        'name'   => 'formPostInicio',
        'method' => 'post',
        'action' => $url_iniciar,
        'style'  => 'margin-top:20px;'
    ));
    echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'RutOtec',           'value' => $rutotec));
    echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'Token',             'value' => $token));
    echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'CodSence',          'value' => $codigosence));
    echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'CodigoCurso',       'value' => $course->shortname));
    echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'LineaCapacitacion', 'value' => $lineacap));
    echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'RunAlumno',         'value' => $runalumno));
    echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'IdSesionAlumno',    'value' => $idsesionalumno));
    echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'UrlRetoma',         'value' => $urlretoma));
    echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'UrlError',          'value' => $urlerror));
    echo html_writer::empty_tag('input', array(
        'type'  => 'submit',
        'value' => get_string('ir_sence', 'local_senceasistencia'),
        'class' => 'btn btn-primary',
        'style' => 'padding:12px 24px;font-size:16px;font-weight:bold;cursor:pointer;'
    ));
    echo html_writer::end_tag('form');
}

// ==================== BOTÓN CIERRE ====================
if ($sesionactiva && !$tienecierre) {
    echo html_writer::tag('p', get_string('sence_session_activa', 'local_senceasistencia'), array(
        'style' => 'color:' . $color_principal . ';font-weight:bold;'
    ));

    echo html_writer::start_tag('form', array(
        'name'   => 'formPostCierre',
        'method' => 'post',
        'action' => $url_cerrar,
        'style'  => 'margin-top:20px;'
    ));
    echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'RutOtec',           'value' => $rutotec));
    echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'Token',             'value' => $token));
    echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'CodSence',          'value' => $codigosence));
    echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'CodigoCurso',       'value' => $course->shortname));
    echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'LineaCapacitacion', 'value' => $lineacap));
    echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'RunAlumno',         'value' => $runalumno));
    echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'IdSesionAlumno',    'value' => $sesionactiva->sence_idsessionalumno));
    echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'IdSesionSence',     'value' => $sesionactiva->sence_idsession));
    echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'UrlRetoma',         'value' => $urlretoma));
    echo html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'UrlError',          'value' => $urlerror));
    echo html_writer::empty_tag('input', array(
        'type'  => 'submit',
        'value' => get_string('ir_sence_cerrar', 'local_senceasistencia'),
        'class' => 'btn btn-warning',
        'style' => 'padding:12px 24px;font-size:16px;font-weight:bold;cursor:pointer;'
    ));
    echo html_writer::end_tag('form');
}

echo html_writer::end_div();

echo $OUTPUT->footer();