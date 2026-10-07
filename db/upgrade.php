<?php
// db/upgrade.php - Actualizaciones de la base de datos del plugin
defined('MOODLE_INTERNAL') || die();

/**
 * Función de upgrade del plugin local_senceasistencia.
 *
 * @param int $oldversion Versión anterior del plugin
 * @return bool
 */
function xmldb_local_senceasistencia_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    // ==================== VERSIÓN 2026092704 ====================
    // Agregar tabla local_sence_tokens
    if ($oldversion < 2026092704) {

        // Definir la tabla
        $table = new xmldb_table('local_sence_tokens');

        // Campos
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('token', XMLDB_TYPE_CHAR, '64', null, XMLDB_NOTNULL, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('idsesionalumno', XMLDB_TYPE_CHAR, '149', null, null, null, null);
        $table->add_field('accion', XMLDB_TYPE_CHAR, '20', null, XMLDB_NOTNULL, null, 'inicio');
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timeexpires', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Claves
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Índices
        $table->add_index('token', XMLDB_INDEX_UNIQUE, array('token'));
        $table->add_index('userid-courseid', XMLDB_INDEX_NOTUNIQUE, array('userid', 'courseid'));

        // Crear la tabla si no existe
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Guardar el punto de actualización
        upgrade_plugin_savepoint(true, 2026092704, 'local', 'senceasistencia');
    }

    // ==================== VERSIÓN 2026093000 ====================
    // Agregar columna 'duracion' a local_sence_asistencia
    if ($oldversion < 2026093000) {

        $table = new xmldb_table('local_sence_asistencia');

        // Agregar columna duracion (en segundos)
        $field = new xmldb_field('duracion', XMLDB_TYPE_INTEGER, '10', null, null, null, '0', 'timecreated');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Guardar el punto de actualización
        upgrade_plugin_savepoint(true, 2026093000, 'local', 'senceasistencia');
    }

    return true;
}