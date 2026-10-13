<?php
// admin_cerrar_sesiones.php - Panel de administración para cerrar sesiones huérfanas
require_once('../../config.php');
require_once($CFG->dirroot . '/local/senceasistencia/locallib.php');

require_login();
require_capability('moodle/site:config', context_system::instance());

$action      = optional_param('action', '', PARAM_ALPHA);
$horas       = optional_param('horas', 24, PARAM_INT);
$courseid    = optional_param('courseid', 0, PARAM_INT);
$confirmado  = optional_param('confirmado', 0, PARAM_INT);

$url = new moodle_url('/local/senceasistencia/admin_cerrar_sesiones.php');
$PAGE->set_url($url);
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('admin_cerrar_sesiones', 'local_senceasistencia'));
$PAGE->set_heading(get_string('admin_cerrar_sesiones', 'local_senceasistencia'));
$PAGE->set_pagelayout('admin');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('admin_cerrar_sesiones', 'local_senceasistencia'));

// ==================== FILTROS ====================
echo html_writer::start_div('', array('style' => 'background:#f8f9fa;padding:15px;border-radius:8px;margin-bottom:20px;'));

echo html_writer::tag('p', get_string('admin_cerrar_sesiones_desc', 'local_senceasistencia'), array(
    'style' => 'font-size:14px;color:#333;'
));

echo '<form method="get" action="' . $url . '" style="display:flex;gap:15px;align-items:end;flex-wrap:wrap;">';

// Campo: horas mínimas
echo '<div>';
echo '<label style="display:block;font-size:13px;color:#666;">' . get_string('admin_horas_minimas', 'local_senceasistencia') . '</label>';
echo '<input type="number" name="horas" value="' . $horas . '" min="1" max="720" style="padding:8px;width:100px;">';
echo '</div>';

// Campo: curso (opcional)
echo '<div>';
echo '<label style="display:block;font-size:13px;color:#666;">' . get_string('admin_curso_opcional', 'local_senceasistencia') . '</label>';
$cursos = $DB->get_records_sql("SELECT DISTINCT c.id, c.fullname, c.shortname
                                FROM {course} c
                                JOIN {local_sence_asistencia} a ON a.courseid = c.id
                                WHERE c.id > 1
                                ORDER BY c.fullname");
$options = array(0 => get_string('admin_todos_cursos', 'local_senceasistencia'));
foreach ($cursos as $c) {
    $options[$c->id] = $c->fullname . ' (' . $c->shortname . ')';
}
echo html_writer::select($options, 'courseid', $courseid, false, array('style' => 'padding:8px;min-width:250px;'));
echo '</div>';

echo '<div>';
echo '<button type="submit" style="padding:8px 20px;background:#0f6cbf;color:white;border:none;border-radius:4px;cursor:pointer;font-weight:bold;">'
   . get_string('admin_buscar', 'local_senceasistencia') . '</button>';
echo '</div>';

echo '</form>';
echo html_writer::end_div();

// ==================== BUSCAR SESIONES ABIERTAS ====================
$params = array(
    'horas_limite' => time() - ($horas * 3600),
);
$sql_filter_course = '';
if ($courseid > 0) {
    $sql_filter_course = ' AND i.courseid = :courseid ';
    $params['courseid'] = $courseid;
}

$sql = "SELECT i.id AS inicio_id,
               i.userid,
               i.courseid,
               i.ambiente,
               i.sence_idsession,
               i.sence_idsessionalumno,
               i.runalumno,
               i.sence_codsence,
               i.zonahoraria,
               i.timecreated AS inicio_time,
               u.firstname,
               u.lastname,
               u.username,
               c.fullname AS course_name,
               c.shortname AS course_shortname
        FROM {local_sence_asistencia} i
        JOIN {user} u ON u.id = i.userid
        JOIN {course} c ON c.id = i.courseid
        WHERE i.accion = 'inicio'
          AND i.timecreated < :horas_limite
          AND NOT EXISTS (
              SELECT 1 
              FROM {local_sence_asistencia} cx
              WHERE cx.accion IN ('cierre', 'cierre_automatico', 'cierre_auto', 'cierre_administrativo')
                AND cx.userid = i.userid
                AND cx.courseid = i.courseid
                AND cx.ambiente = i.ambiente
                AND cx.timecreated > i.timecreated
          )
          $sql_filter_course
        ORDER BY i.timecreated DESC";

$sesiones = $DB->get_records_sql($sql, $params);

if (empty($sesiones)) {
    echo $OUTPUT->notification(get_string('admin_no_sesiones', 'local_senceasistencia'), 'notifysuccess');
    echo $OUTPUT->footer();
    exit;
}

echo html_writer::tag('h3', get_string('admin_sesiones_encontradas', 'local_senceasistencia', count($sesiones)));

// ==================== EJECUTAR CIERRE ====================
if ($action === 'cerrar' && $confirmado) {
    require_sesskey();

    $ahora = time();
    $cerradas = 0;
    $errores = 0;

    foreach ($sesiones as $s) {
        $record = new stdClass();
        $record->courseid              = $s->courseid;
        $record->userid                = $s->userid;
        $record->sence_codsence        = $s->sence_codsence;
        $record->sence_idsession       = $s->sence_idsession;
        $record->sence_idsessionalumno = $s->sence_idsessionalumno;
        $record->runalumno             = $s->runalumno;
        $record->accion                = 'cierre_administrativo';
        $record->glosaerror            = null;
        $record->ambiente              = $s->ambiente;
        $record->fechahora             = date('Y-m-d H:i:s', $ahora);
        $record->zonahoraria           = $s->zonahoraria;
        $record->timecreated           = $ahora;
        $record->duracion              = 0;

        try {
            $DB->insert_record('local_sence_asistencia', $record);
            $cerradas++;
        } catch (Exception $e) {
            $errores++;
            debugging('Error cerrando sesión inicio_id=' . $s->inicio_id . ': ' . $e->getMessage(), DEBUG_DEVELOPER);
        }
    }

    echo $OUTPUT->notification(
        get_string('admin_resultado_cierre', 'local_senceasistencia', array('cerradas' => $cerradas, 'errores' => $errores)),
        'notifysuccess'
    );

    echo html_writer::div(
        html_writer::link($url, get_string('admin_volver', 'local_senceasistencia'), 
            array('class' => 'btn btn-primary')),
        '',
        array('style' => 'text-align:center;margin-top:20px;')
    );

    echo $OUTPUT->footer();
    exit;
}

// ==================== MOSTRAR TABLA ====================
$table = new html_table();
$table->head = array(
    get_string('admin_col_usuario', 'local_senceasistencia'),
    get_string('admin_col_curso', 'local_senceasistencia'),
    get_string('admin_col_run', 'local_senceasistencia'),
    get_string('admin_col_ambiente', 'local_senceasistencia'),
    get_string('admin_col_fecha_inicio', 'local_senceasistencia'),
    get_string('admin_col_horas', 'local_senceasistencia'),
    get_string('admin_col_session', 'local_senceasistencia'),
);
$table->attributes['class'] = 'table table-striped';

foreach ($sesiones as $s) {
    $horas_abierta = round((time() - $s->inicio_time) / 3600, 1);
    $table->data[] = array(
        s($s->firstname . ' ' . $s->lastname) . '<br><small style="color:#666;">' . s($s->username) . ' (ID: ' . $s->userid . ')</small>',
        s($s->course_name) . '<br><small style="color:#666;">' . s($s->course_shortname) . '</small>',
        '<code>' . s($s->runalumno) . '</code>',
        '<span style="background:' . (($s->ambiente === 'prod') ? '#28a745' : '#ffc107') . ';color:white;padding:2px 8px;border-radius:10px;font-size:11px;">' . s($s->ambiente) . '</span>',
        userdate($s->inicio_time, get_string('strftimedatetime', 'langconfig')),
        '<strong>' . $horas_abierta . 'h</strong>',
        '<code style="font-size:11px;">' . s(substr($s->sence_idsession, 0, 16)) . '...</code>',
    );
}

echo html_writer::table($table);

// ==================== BOTÓN DE CONFIRMACIÓN ====================
$confirm_url = new moodle_url($url, array(
    'action' => 'cerrar',
    'horas' => $horas,
    'courseid' => $courseid,
    'confirmado' => 1,
    'sesskey' => sesskey(),
));

echo html_writer::start_div('', array('style' => 'text-align:center;margin-top:30px;padding:20px;background:#fff3cd;border-radius:8px;'));

echo html_writer::tag('p', get_string('admin_confirmar_desc', 'local_senceasistencia', count($sesiones)), array(
    'style' => 'font-size:16px;font-weight:bold;color:#856404;'
));

echo html_writer::tag('p', get_string('admin_confirmar_warning', 'local_senceasistencia'), array(
    'style' => 'font-size:13px;color:#856404;'
));

echo html_writer::link($confirm_url, get_string('admin_confirmar_btn', 'local_senceasistencia', count($sesiones)), array(
    'class' => 'btn btn-danger',
    'style' => 'padding:12px 30px;font-size:16px;font-weight:bold;',
    'onclick' => 'return confirm("' . get_string('admin_confirm_js', 'local_senceasistencia', count($sesiones)) . '");'
));

echo html_writer::end_div();

echo $OUTPUT->footer();