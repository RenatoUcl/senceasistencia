<?php
// db/hooks.php - Registro de hooks de Moodle 4.4+
defined('MOODLE_INTERNAL') || die();

$callbacks = [
    [
        'hook' => \core\hook\output\before_standard_top_of_body_html_generation::class,
        'callback' => [\local_senceasistencia\hook_callbacks::class, 'before_standard_top_of_body'],
        'priority' => 500,
    ],
];