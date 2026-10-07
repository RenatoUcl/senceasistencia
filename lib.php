<?php
// lib.php - Integración con el menú de administración del curso
defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/locallib.php');

/**
 * Añade el enlace al plugin en el menú de administración del curso.
 * Solo aparece si el curso tiene un código SENCE configurado.
 *
 * @param settings_navigation $settingsnav
 * @param context $context
 */
function local_senceasistencia_extend_settings_navigation($settingsnav, $context) {
    global $PAGE;

    // Solo en cursos reales
    if (!$PAGE->course || $PAGE->course->id == 1) {
        return;
    }

    // Solo si el usuario puede ver el curso
    if (!has_capability('moodle/course:view', $context)) {
        return;
    }

    // Verificar que el curso tenga código SENCE
    $sencecod = local_senceasistencia_get_codigo_sence($PAGE->course->id);
    if (empty($sencecod)) {
        return;
    }

    // Añadir el enlace al menú de administración del curso
    if ($settingnode = $settingsnav->find('courseadmin', navigation_node::TYPE_COURSE)) {
        $url = new moodle_url('/local/senceasistencia/index.php', array('id' => $PAGE->course->id));
        $node = navigation_node::create(
            get_string('pluginname', 'local_senceasistencia'),
            $url,
            navigation_node::TYPE_SETTING,
            null,
            'senceasistencia',
            new pix_icon('i/settings', '')
        );
        $settingnode->add_node($node);
    }
}