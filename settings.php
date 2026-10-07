<?php
// settings.php - Panel de configuración del plugin
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {

    $settings = new admin_settingpage(
        'local_senceasistencia',
        get_string('pluginname', 'local_senceasistencia')
    );

    $ADMIN->add('localplugins', $settings);

    // ==================== SELECTOR DE AMBIENTE ====================
    $settings->add(new admin_setting_heading(
        'local_senceasistencia/ambiente_heading',
        get_string('ambiente', 'local_senceasistencia'),
        get_string('ambiente_desc', 'local_senceasistencia')
    ));

    $settings->add(new admin_setting_configselect(
        'local_senceasistencia/ambiente',
        get_string('ambiente', 'local_senceasistencia'),
        get_string('ambiente_selector_desc', 'local_senceasistencia'),
        'test',
        array(
            'test' => get_string('ambiente_test', 'local_senceasistencia'),
            'prod' => get_string('ambiente_prod', 'local_senceasistencia'),
        )
    ));

    // ==================== URLs TEST ====================
    $settings->add(new admin_setting_heading(
        'local_senceasistencia/urls_test_heading',
        get_string('urls_test_heading', 'local_senceasistencia'),
        ''
    ));

    $settings->add(new admin_setting_configtext(
        'local_senceasistencia/url_iniciar_test',
        get_string('sence_url_iniciar_test', 'local_senceasistencia'),
        get_string('sence_url_iniciar_test_desc', 'local_senceasistencia'),
        'https://sistemas.sence.cl/rctest/Registro/IniciarSesion',
        PARAM_URL
    ));

    $settings->add(new admin_setting_configtext(
        'local_senceasistencia/url_cerrar_test',
        get_string('sence_url_cerrar_test', 'local_senceasistencia'),
        get_string('sence_url_cerrar_test_desc', 'local_senceasistencia'),
        'https://sistemas.sence.cl/rctest/Registro/CerrarSesion',
        PARAM_URL
    ));

    // ==================== URLs PRODUCCIÓN ====================
    $settings->add(new admin_setting_heading(
        'local_senceasistencia/urls_prod_heading',
        get_string('urls_prod_heading', 'local_senceasistencia'),
        ''
    ));

    $settings->add(new admin_setting_configtext(
        'local_senceasistencia/url_iniciar_prod',
        get_string('sence_url_iniciar_prod', 'local_senceasistencia'),
        get_string('sence_url_iniciar_prod_desc', 'local_senceasistencia'),
        'https://sistemas.sence.cl/rce/Registro/IniciarSesion',
        PARAM_URL
    ));

    $settings->add(new admin_setting_configtext(
        'local_senceasistencia/url_cerrar_prod',
        get_string('sence_url_cerrar_prod', 'local_senceasistencia'),
        get_string('sence_url_cerrar_prod_desc', 'local_senceasistencia'),
        'https://sistemas.sence.cl/rce/Registro/CerrarSesion',
        PARAM_URL
    ));

    // ==================== DATOS DEL OTEC ====================
    $settings->add(new admin_setting_heading(
        'local_senceasistencia/otec_heading',
        get_string('otec_heading', 'local_senceasistencia'),
        ''
    ));

    $settings->add(new admin_setting_configtext(
        'local_senceasistencia/rut_otec',
        get_string('sence_rut_otec', 'local_senceasistencia'),
        get_string('sence_rut_otec_desc', 'local_senceasistencia'),
        '',
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configtext(
        'local_senceasistencia/token',
        get_string('sence_token', 'local_senceasistencia'),
        get_string('sence_token_desc', 'local_senceasistencia'),
        '',
        PARAM_TEXT
    ));

    $settings->add(new admin_setting_configselect(
        'local_senceasistencia/linea_default',
        get_string('sence_linea_default', 'local_senceasistencia'),
        get_string('sence_linea_default_desc', 'local_senceasistencia'),
        3,
        array(
            1 => 'Programas Sociales',
            3 => 'Franquicia Tributaria',
            6 => 'FPT',
        )
    ));

    // ==================== CAMPOS PERSONALIZADOS ====================
    $settings->add(new admin_setting_heading(
        'local_senceasistencia/campos_heading',
        get_string('campos_heading', 'local_senceasistencia'),
        ''
    ));

    $settings->add(new admin_setting_configtext(
        'local_senceasistencia/campo_curso',
        get_string('sence_campo_curso', 'local_senceasistencia'),
        get_string('sence_campo_curso_desc', 'local_senceasistencia'),
        'sence_codigo',
        PARAM_ALPHANUMEXT
    ));

    $settings->add(new admin_setting_configtext(
        'local_senceasistencia/campo_rut',
        get_string('sence_campo_rut', 'local_senceasistencia'),
        get_string('sence_campo_rut_desc', 'local_senceasistencia'),
        'rut',
        PARAM_ALPHANUMEXT
    ));

    $settings->add(new admin_setting_configtext(
        'local_senceasistencia/campo_dv',
        get_string('sence_campo_dv', 'local_senceasistencia'),
        get_string('sence_campo_dv_desc', 'local_senceasistencia'),
        'dv',
        PARAM_ALPHANUMEXT
    ));

    // ==================== DURACIÓN DE SESIÓN ====================
    $settings->add(new admin_setting_heading(
        'local_senceasistencia/duracion_heading',
        get_string('duracion_heading', 'local_senceasistencia'),
        get_string('duracion_heading_desc', 'local_senceasistencia')
    ));

    $settings->add(new admin_setting_configtext(
        'local_senceasistencia/duracion_sesion',
        get_string('duracion_sesion', 'local_senceasistencia'),
        get_string('duracion_sesion_desc', 'local_senceasistencia'),
        '120',  // 2 horas por defecto
        PARAM_INT
    ));
}