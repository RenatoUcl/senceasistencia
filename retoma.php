<?php
// retoma.php - Página de retorno exitoso desde SENCE
require_once('../../config.php');
require_once($CFG->dirroot . '/local/senceasistencia/locallib.php');

$id    = required_param('id', PARAM_INT);
$token = optional_param('token', '', PARAM_ALPHANUM);

$course = $DB->get_record('course', array('id' => $id), '*', MUST_EXIST);

// ==================== VALIDAR TOKEN ====================
$tokenrecord = local_senceasistencia_validar_token($token, $id);

if ($tokenrecord) {
    // Token válido: autenticar al usuario en la MISMA petición
    $userid = $tokenrecord->userid;
    $user = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);
    \core\session\manager::set_user($user);
}

// Asegurar que el usuario esté autenticado
require_login($course);

$context = context_course::instance($id);
$PAGE->set_url('/local/senceasistencia/retoma.php', array('id' => $id));
$PAGE->set_context($context);
$PAGE->set_title(get_string('registro_exitoso', 'local_senceasistencia'));
$PAGE->set_heading($course->fullname);

// ==================== LECTURA DE PARÁMETROS DE SENCE ====================
$codigosence = optional_param('CodSence', '', PARAM_TEXT);
$idcurso     = optional_param('CodigoCurso', '', PARAM_TEXT);
$runalumno   = optional_param('RunAlumno', '', PARAM_TEXT);
$fechahora   = optional_param('FechaHora', '', PARAM_TEXT);
$zonahoraria = optional_param('ZonaHoraria', '', PARAM_TEXT);
$lineacap    = optional_param('LineaCapacitacion', 0, PARAM_INT);

// IdSesionSence (una sola "s")
$idsesionsence = optional_param('IdSesionSence', '', PARAM_TEXT);
if (empty($idsesionsence)) {
    $idsesionsence = optional_param('IdSessionSence', '', PARAM_TEXT);
}

// IdSesionAlumno (una sola "s")
$idsesionalumno = optional_param('IdSesionAlumno', '', PARAM_TEXT);
if (empty($idsesionalumno)) {
    $idsesionalumno = optional_param('IdSessionAlumno', '', PARAM_TEXT);
}

$ambiente = local_senceasistencia_get_ambiente();

// ==================== DETECTAR INICIO O CIERRE ====================
// La acción viene del token (más confiable que depender de SENCE)
$accion = 'inicio';
if ($tokenrecord && !empty($tokenrecord->accion)) {
    $accion = $tokenrecord->accion;
}

// ==================== SI ES CIERRE: BUSCAR EL ÚLTIMO INICIO ABIERTO ====================
// SENCE puede generar un sence_idsession NUEVO en cada operación,
// así que NO podemos asociar por sence_idsession.
// Buscamos el último inicio sin cierre posterior por userid+courseid+ambiente.
$registro_inicio = null;

if ($accion === 'cierre') {
    $sql = "SELECT i.*
            FROM {local_sence_asistencia} i
            WHERE i.accion = 'inicio'
              AND i.userid = :userid
              AND i.courseid = :courseid
              AND i.ambiente = :ambiente
              AND NOT EXISTS (
                  SELECT 1 
                  FROM {local_sence_asistencia} c
                  WHERE c.accion IN ('cierre', 'cierre_automatico', 'cierre_auto', 'cierre_administrativo')
                    AND c.userid = i.userid
                    AND c.courseid = i.courseid
                    AND c.ambiente = i.ambiente
                    AND c.timecreated > i.timecreated
              )
            ORDER BY i.timecreated DESC";

    $registro_inicio = $DB->get_record_sql($sql, array(
        'userid'   => $USER->id,
        'courseid' => $id,
        'ambiente' => $ambiente,
    ), IGNORE_MULTIPLE);

    // Si SENCE no devolvió IdSesionSence, recuperarlo del inicio abierto
    if (empty($idsesionsence) && $registro_inicio && !empty($registro_inicio->sence_idsession)) {
        $idsesionsence = $registro_inicio->sence_idsession;
    }

    // Si SENCE no devolvió IdSesionAlumno, recuperarlo del inicio abierto
    if (empty($idsesionalumno) && $registro_inicio && !empty($registro_inicio->sence_idsessionalumno)) {
        $idsesionalumno = $registro_inicio->sence_idsessionalumno;
    }
}

// Eliminar el token (un solo uso)
if ($tokenrecord) {
    local_senceasistencia_eliminar_token($token);
}

// ==================== GUARDAR EN BASE DE DATOS ====================
$tiene_datos = !empty($codigosence) || !empty($runalumno) || !empty($idsesionsence);

if ($accion === 'inicio' && !$tiene_datos) {
    echo $OUTPUT->header();
    echo $OUTPUT->notification('No se recibieron datos de SENCE.', 'notifyproblem');
    echo $OUTPUT->footer();
    exit;
}

$record = new stdClass();
$record->courseid              = $id;
$record->userid                = $USER->id;
$record->sence_codsence        = $codigosence;
$record->sence_idsession       = $idsesionsence;
$record->sence_idsessionalumno = $idsesionalumno;
$record->runalumno             = $runalumno;
$record->accion                = $accion;
$record->glosaerror            = null;
$record->ambiente              = $ambiente;
$record->fechahora             = $fechahora;
$record->zonahoraria           = $zonahoraria;
$record->timecreated           = time();

// Guardar duración (solo para inicios)
if ($accion === 'inicio') {
    $duracion_min = (int)get_config('local_senceasistencia', 'duracion_sesion');
    if (empty($duracion_min)) { $duracion_min = 240; }
    $record->duracion = $duracion_min * 60;
} else {
    $record->duracion = 0;
}

try {
    $DB->insert_record('local_sence_asistencia', $record);
} catch (Exception $e) {
    debugging('Error guardando registro SENCE: ' . $e->getMessage(), DEBUG_DEVELOPER);
}

// Guardar en sesión
if (!empty($idsesionsence)) {
    $SESSION->sence_idsession = $idsesionsence;
}

// ==================== MOSTRAR AL USUARIO ====================
echo $OUTPUT->header();

if ($accion == 'inicio') {
    echo $OUTPUT->notification(get_string('inicio_exitoso', 'local_senceasistencia'), 'notifysuccess');
} else {
    echo $OUTPUT->notification(get_string('cierre_exitoso', 'local_senceasistencia'), 'notifysuccess');
}

echo html_writer::start_div('', array(
    'style' => 'max-width:600px;margin:20px auto;padding:20px;'
             . 'border:1px solid #ccc;border-radius:8px;background:#f9f9f9;'
));

echo html_writer::tag('p', html_writer::tag('strong', 'CodSence: ') . s($codigosence));
echo html_writer::tag('p', html_writer::tag('strong', 'CodigoCurso: ') . s($idcurso));
echo html_writer::tag('p', html_writer::tag('strong', 'IdSesionSence: ') . s($idsesionsence));
echo html_writer::tag('p', html_writer::tag('strong', 'RunAlumno: ') . s($runalumno));
echo html_writer::tag('p', html_writer::tag('strong', 'FechaHora: ') . s($fechahora));
echo html_writer::tag('p', html_writer::tag('strong', 'ZonaHoraria: ') . s($zonahoraria));
echo html_writer::tag('p', html_writer::tag('strong', 'LineaCapacitacion: ') . s($lineacap));

echo html_writer::end_div();

echo html_writer::div(
    html_writer::link(
        new moodle_url('/course/view.php', array('id' => $id)),
        get_string('volver_al_curso', 'local_senceasistencia'),
        array('class' => 'btn btn-primary')
    ),
    '',
    array('style' => 'text-align:center;margin-top:20px;')
);

echo $OUTPUT->footer();