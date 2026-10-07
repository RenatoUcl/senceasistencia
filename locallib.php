<?php
// locallib.php - Funciones auxiliares del plugin
defined('MOODLE_INTERNAL') || die();

/**
 * Devuelve la URL de inicio de sesión según el ambiente configurado.
 *
 * @return string
 */
function local_senceasistencia_get_url_iniciar() {
    $ambiente = local_senceasistencia_get_ambiente();
    if ($ambiente === 'prod') {
        return get_config('local_senceasistencia', 'url_iniciar_prod');
    }
    return get_config('local_senceasistencia', 'url_iniciar_test');
}

/**
 * Devuelve la URL de cierre de sesión según el ambiente configurado.
 *
 * @return string
 */
function local_senceasistencia_get_url_cerrar() {
    $ambiente = local_senceasistencia_get_ambiente();
    if ($ambiente === 'prod') {
        return get_config('local_senceasistencia', 'url_cerrar_prod');
    }
    return get_config('local_senceasistencia', 'url_cerrar_test');
}

/**
 * Devuelve el ambiente actual ('test' o 'prod').
 *
 * @return string
 */
function local_senceasistencia_get_ambiente() {
    $ambiente = get_config('local_senceasistencia', 'ambiente');
    return ($ambiente === 'prod') ? 'prod' : 'test';
}

/**
 * Obtiene el código SENCE de un curso.
 * Primero busca en el campo personalizado de curso configurado.
 * Si no existe, usa el idnumber del curso.
 *
 * @param int $courseid
 * @return string
 */
function local_senceasistencia_get_codigo_sence($courseid) {
    global $DB;

    $campo = get_config('local_senceasistencia', 'campo_curso');
    if (empty($campo)) {
        $campo = 'sence_codigo';
    }

    try {
        $field = $DB->get_record('customfield_field', array('shortname' => $campo), 'id', IGNORE_MISSING);
        if ($field) {
            $sql = "SELECT cd.value
                    FROM {customfield_data} cd
                    WHERE cd.fieldid = :fieldid
                      AND cd.instanceid = :courseid";
            $value = $DB->get_field_sql($sql, array(
                'fieldid'  => $field->id,
                'courseid' => $courseid
            ), IGNORE_MISSING);
            if (!empty($value)) {
                return trim($value);
            }
        }

        $idnumber = $DB->get_field('course', 'idnumber', array('id' => $courseid), IGNORE_MISSING);
        return $idnumber ? trim($idnumber) : '';
    } catch (Exception $e) {
        return '';
    }
}

/**
 * Obtiene el RUN del alumno en formato SENCE (xxxxxxxx-x).
 * Lee desde los campos personalizados configurados (por defecto: rut y dv).
 * Si no los encuentra, usa el idnumber del usuario.
 *
 * @param int $userid
 * @return string
 */
/*
function local_senceasistencia_get_run_alumno($userid) {
    global $DB;

    $campo_rut = get_config('local_senceasistencia', 'campo_rut');
    $campo_dv  = get_config('local_senceasistencia', 'campo_dv');
    if (empty($campo_rut)) { $campo_rut = 'rut'; }
    if (empty($campo_dv))  { $campo_dv = 'dv'; }

    try {
        $field_rut = $DB->get_record('user_info_field', array('shortname' => $campo_rut), 'id', IGNORE_MISSING);
        $field_dv  = $DB->get_record('user_info_field', array('shortname' => $campo_dv), 'id', IGNORE_MISSING);

        if ($field_rut && $field_dv) {
            $sql = "SELECT uid.data AS rut, udv.data AS dv
                    FROM {user_info_data} uid
                    JOIN {user_info_data} udv ON udv.userid = uid.userid
                    WHERE uid.userid = :userid
                      AND uid.fieldid = :fieldid_rut
                      AND udv.fieldid = :fieldid_dv";
            $result = $DB->get_record_sql($sql, array(
                'userid'      => $userid,
                'fieldid_rut' => $field_rut->id,
                'fieldid_dv'  => $field_dv->id
            ), IGNORE_MISSING);

            if ($result && !empty($result->rut) && !empty($result->dv)) {
                $rut = preg_replace('/[^0-9kK]/', '', $result->rut);
                $dv  = preg_replace('/[^0-9kK]/', '', $result->dv);
                return $rut . '-' . strtoupper($dv);
            }
        }

        // Respaldo: idnumber del usuario
        $idnumber = $DB->get_field('user', 'idnumber', array('id' => $userid), IGNORE_MISSING);
        if (!empty($idnumber)) {
            return str_replace(array('.', ' '), '', $idnumber);
        }
    } catch (Exception $e) {
        return '';
    }

    return '';
}
*/

/**
 * Obtiene el RUN del alumno en formato SENCE (xxxxxxxx-x).
 * Lee desde los campos personalizados configurados (por defecto: rut y dv).
 * Si no los encuentra, usa el idnumber del usuario.
 *
 * IMPORTANTE: El DV "0" es un valor válido y debe procesarse correctamente.
 * No usar empty() porque empty("0") devuelve true en PHP.
 *
 * @param int $userid
 * @return string
 */
function local_senceasistencia_get_run_alumno($userid) {
    global $DB;

    $campo_rut = get_config('local_senceasistencia', 'campo_rut');
    $campo_dv  = get_config('local_senceasistencia', 'campo_dv');
    if (empty($campo_rut)) { $campo_rut = 'rut'; }
    if (empty($campo_dv))  { $campo_dv = 'dv'; }

    try {
        $field_rut = $DB->get_record('user_info_field', array('shortname' => $campo_rut), 'id', IGNORE_MISSING);
        $field_dv  = $DB->get_record('user_info_field', array('shortname' => $campo_dv), 'id', IGNORE_MISSING);

        if ($field_rut && $field_dv) {
            $sql = "SELECT uid.data AS rut, udv.data AS dv
                    FROM {user_info_data} uid
                    JOIN {user_info_data} udv ON udv.userid = uid.userid
                    WHERE uid.userid = :userid
                      AND uid.fieldid = :fieldid_rut
                      AND udv.fieldid = :fieldid_dv";
            $result = $DB->get_record_sql($sql, array(
                'userid'      => $userid,
                'fieldid_rut' => $field_rut->id,
                'fieldid_dv'  => $field_dv->id
            ), IGNORE_MISSING);

            if ($result) {
                // ⚠️ CAMBIO CRÍTICO: usar comparación estricta con '' en lugar de empty()
                $rut_raw = isset($result->rut) ? trim($result->rut) : '';
                $dv_raw  = isset($result->dv)  ? trim($result->dv)  : '';

                // Validar que AMBOS tengan datos (comparación estricta)
                // "0" no debe considerarse vacío
                if ($rut_raw !== '' && $dv_raw !== '') {
                    // Limpiar el RUT de puntos y espacios, pero mantener números y K
                    $rut = preg_replace('/[^0-9kK]/', '', $rut_raw);
                    // Limpiar el DV de espacios, pero mantener números y K
                    $dv  = preg_replace('/[^0-9kK]/', '', $dv_raw);
                    
                    // Validación final: que el RUT tenga al menos 1 dígito
                    if ($rut !== '') {
                        return $rut . '-' . strtoupper($dv);
                    }
                }
            }
        }

        // Respaldo: usar idnumber del usuario
        $idnumber = $DB->get_field('user', 'idnumber', array('id' => $userid), IGNORE_MISSING);
        if (!empty($idnumber)) {
            return str_replace(array('.', ' '), '', $idnumber);
        }
    } catch (Exception $e) {
        return '';
    }

    return '';
}


/**
 * Verifica que el plugin esté completamente configurado.
 *
 * @return bool
 */
function local_senceasistencia_config_ok() {
    $rutotec = get_config('local_senceasistencia', 'rut_otec');
    $token   = get_config('local_senceasistencia', 'token');
    $urlin   = local_senceasistencia_get_url_iniciar();
    $urlout  = local_senceasistencia_get_url_cerrar();

    return !empty($rutotec) && !empty($token) && !empty($urlin) && !empty($urlout);
}

/**
 * Obtiene la línea de capacitación por defecto.
 *
 * @return int
 */
function local_senceasistencia_get_linea_default() {
    $linea = get_config('local_senceasistencia', 'linea_default');
    if (empty($linea)) {
        $linea = 3; // Franquicia Tributaria por defecto
    }
    return (int)$linea;
}

/**
 * Genera un IdSesionAlumno único para el envío.
 * Nota: "IdSesionAlumno" lleva UNA SOLA "s" en "Sesion".
 *
 * @param int $userid
 * @return string
 */
function local_senceasistencia_generar_idsesion_alumno($userid) {
    return 'MDL-' . $userid . '-' . time();
}

/**
 * Devuelve el mapa de errores de SENCE (Anexo 2 del manual v1.1.6).
 *
 * @return array
 */
function local_senceasistencia_get_errores_sence() {
    return array(
        200 => 'El POST tiene uno o más parámetros mandatorios sin información.',
        201 => 'La URL de Retoma y/o URL de Error no tienen información.',
        202 => 'La URL de Retoma tiene formato incorrecto.',
        203 => 'La URL de Error tiene formato incorrecto.',
        204 => 'El Código SENCE tiene menos de 10 caracteres y/o no es código válido.',
        205 => 'El Código Curso tiene menos de 7 caracteres y/o no es código válido.',
        206 => 'La línea de capacitación es incorrecta.',
        207 => 'El Run Alumno tiene formato incorrecto, o tiene el dígito verificador incorrecto.',
        208 => 'El Run Alumno no está autorizado para realizar el curso.',
        209 => 'El Rut OTEC tiene formato incorrecto, o tiene el dígito verificador incorrecto.',
        211 => 'El Token no pertenece al OTEC.',
        212 => 'El Token no está vigente.',
        300 => 'Error interno no clasificado. Reportar al SENCE.',
        301 => 'No se pudo registrar el ingreso o cierre de sesión.',
        302 => 'No se pudo validar la información del Organismo. Reportar al SENCE.',
        303 => 'El Token no existe, o su formato es incorrecto.',
        304 => 'No se pudieron verificar los datos enviados. Reportar al SENCE.',
        305 => 'No se pudo registrar la información. Reportar al SENCE.',
        306 => 'El Código Curso no corresponde al código SENCE.',
        307 => 'El Código Curso no tiene modalidad E-learning.',
        308 => 'El Código Curso no corresponde al RUT OTEC.',
        309 => 'Las fechas de ejecución comunicadas para el Código Curso no corresponden a la fecha actual.',
        310 => 'El Código Curso está en estado Terminado o Anulado.',
        311 => 'Run ingresado en el Login de Clave Única no corresponde con Run alumno informado por el ejecutor.',
        312 => 'No se pudo completar la autenticación con Clave Única.',
        313 => 'URL de Cierre de sesión Incorrecta.',
    );
}

/**
 * Devuelve la glosa de un código de error.
 *
 * @param int $codigo
 * @return string
 */
function local_senceasistencia_get_glosa_error($codigo) {
    $errores = local_senceasistencia_get_errores_sence();
    if (isset($errores[$codigo])) {
        return $errores[$codigo];
    }
    return 'Error desconocido (código: ' . $codigo . ')';
}

/**
 * Genera un token único de un solo uso para retorno desde SENCE.
 *
 * @param int $userid
 * @param int $courseid
 * @param string $idsesionalumno
 * @param string $accion 'inicio' o 'cierre'
 * @return string Token generado
 */
function local_senceasistencia_crear_token($userid, $courseid, $idsesionalumno = '', $accion = 'inicio') {
    global $DB;

    // Limpiar tokens expirados
    $DB->delete_records_select('local_sence_tokens', 'timeexpires < ?', array(time()));

    // Generar token único
    $token = bin2hex(random_bytes(32)); // 64 caracteres hexadecimales

    $record = new stdClass();
    $record->token           = $token;
    $record->userid          = $userid;
    $record->courseid        = $courseid;
    $record->idsesionalumno  = $idsesionalumno;
    $record->accion          = $accion;
    $record->timecreated     = time();
    $record->timeexpires     = time() + 1800; // 30 minutos

    $DB->insert_record('local_sence_tokens', $record);

    return $token;
}

/**
 * Valida un token de retorno.
 *
 * @param string $token
 * @param int $courseid
 * @return \stdClass|false Registro del token o false si inválido
 */
function local_senceasistencia_validar_token($token, $courseid) {
    global $DB;

    if (empty($token)) {
        return false;
    }

    $record = $DB->get_record('local_sence_tokens', array(
        'token'    => $token,
        'courseid' => $courseid
    ));

    if (!$record) {
        return false;
    }

    if ($record->timeexpires < time()) {
        $DB->delete_records('local_sence_tokens', array('id' => $record->id));
        return false;
    }

    return $record;
}

/**
 * Elimina un token después de usarlo.
 *
 * @param string $token
 */
function local_senceasistencia_eliminar_token($token) {
    global $DB;
    $DB->delete_records('local_sence_tokens', array('token' => $token));
}