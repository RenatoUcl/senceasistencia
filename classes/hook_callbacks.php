<?php
// classes/hook_callbacks.php - Callbacks de hooks de Moodle 4.4+
namespace local_senceasistencia;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/senceasistencia/locallib.php');

class hook_callbacks {

    /**
     * Hook que se ejecuta al inicio del <body>.
     * Inyecta el bloque SENCE en páginas de curso.
     *
     * @param \core\hook\output\before_standard_top_of_body_html_generation $hook
     */
    public static function before_standard_top_of_body(
        \core\hook\output\before_standard_top_of_body_html_generation $hook
    ): void {
        global $PAGE, $USER, $COURSE;

        // Detectar el curso correcto usando $COURSE (más confiable que $PAGE->course)
        $courseid = 0;
        if (!empty($COURSE) && !empty($COURSE->id)) {
            $courseid = $COURSE->id;
        } elseif (!empty($PAGE->course) && !empty($PAGE->course->id)) {
            $courseid = $PAGE->course->id;
        }

        // Solo en cursos reales (excluir portada del sitio)
        if (empty($courseid) || $courseid == 1) {
            return;
        }

        // Verificar que el pagetype sea de curso (course-view-*)
        if (strpos($PAGE->pagetype, 'course-view') !== 0) {
            return;
        }

        // No mostrar en modo edición
        if ($PAGE->user_is_editing()) {
            return;
        }

        // No mostrar a invitados
        if (!isloggedin() || isguestuser()) {
            return;
        }

        // Verificar que el plugin esté configurado
        if (!local_senceasistencia_config_ok()) {
            return;
        }

        // Verificar que el curso tenga código SENCE
        $codigosence = local_senceasistencia_get_codigo_sence($courseid);
        if (empty($codigosence)) {
            return;
        }

        // Obtener el objeto del curso
        $course = get_course($courseid);

        // Construir HTML
        $html = self::construir_bloque_sence($course);

        if (!empty($html)) {
            $hook->add_html($html);
        }
    }

    /**
     * Construye el HTML del bloque SENCE.
     *
     * @param \stdClass $course
     * @return string
     */
    public static function construir_bloque_sence($course): string {
        global $DB, $CFG, $USER;

        $courseid = $course->id;

        // ==================== DATOS DEL CURSO ====================
        $codigosence = local_senceasistencia_get_codigo_sence($courseid);

        // ==================== DATOS DEL ALUMNO ====================
        $runalumno = local_senceasistencia_get_run_alumno($USER->id);
        if (empty($runalumno)) {
            return \html_writer::div(
                \get_string('sin_run_alumno', 'local_senceasistencia'),
                'alert alert-warning',
                array('style' => 'margin:20px auto;max-width:600px;')
            );
        }

        // ==================== CONFIGURACIÓN ====================
        $rutotec     = \get_config('local_senceasistencia', 'rut_otec');
        $token       = \get_config('local_senceasistencia', 'token');
        $url_iniciar = \local_senceasistencia_get_url_iniciar();
        $url_cerrar  = \local_senceasistencia_get_url_cerrar();
        $ambiente    = \local_senceasistencia_get_ambiente();
        $lineacap    = \local_senceasistencia_get_linea_default();

        // Duración de la sesión (en minutos)
        $duracion_min = (int)\get_config('local_senceasistencia', 'duracion_sesion');
        if (empty($duracion_min)) {
            $duracion_min = 240;
        }
        $duracion_seg = $duracion_min * 60;

        // Generar IdSesionAlumno único (una sola "s" en "Sesion")
        $idsesionalumno = \local_senceasistencia_generar_idsesion_alumno($USER->id);

        // ==================== VERIFICAR SESIÓN ACTIVA ====================
        $sesionactiva = $DB->get_record('local_sence_asistencia', array(
            'courseid' => $courseid,
            'userid'   => $USER->id,
            'accion'   => 'inicio',
            'ambiente' => $ambiente
        ), '*', IGNORE_MULTIPLE);

        $tienecierre = false;
        if ($sesionactiva) {
            $tienecierre = $DB->record_exists('local_sence_asistencia', array(
                'courseid'        => $courseid,
                'userid'          => $USER->id,
                'accion'          => 'cierre',
                'sence_idsession' => $sesionactiva->sence_idsession,
                'ambiente'        => $ambiente
            ));
        }

        // ==================== GENERAR TOKENS DE RETORNO ====================
        // Token para el formulario de INICIO
        $token_inicio = \local_senceasistencia_crear_token($USER->id, $courseid, $idsesionalumno, 'inicio');
        $urlretoma_inicio = $CFG->wwwroot . '/local/senceasistencia/retoma.php?id=' . $courseid . '&token=' . $token_inicio;
        $urlerror_inicio  = $CFG->wwwroot . '/local/senceasistencia/error.php?id=' . $courseid . '&token=' . $token_inicio;

        // Token para el formulario de CIERRE (solo si hay sesión activa)
        $token_cierre = '';
        $urlretoma_cierre = '';
        $urlerror_cierre = '';
        if ($sesionactiva && !$tienecierre) {
            $token_cierre = \local_senceasistencia_crear_token(
                $USER->id,
                $courseid,
                $sesionactiva->sence_idsessionalumno,
                'cierre'
            );
            $urlretoma_cierre = $CFG->wwwroot . '/local/senceasistencia/retoma.php?id=' . $courseid . '&token=' . $token_cierre;
            $urlerror_cierre  = $CFG->wwwroot . '/local/senceasistencia/error.php?id=' . $courseid . '&token=' . $token_cierre;
        }

        // ==================== COLORES SEGÚN AMBIENTE ====================
        $color_principal = ($ambiente === 'prod') ? '#0f6cbf' : '#6c757d';
        $etiqueta_ambiente = ($ambiente === 'prod')
            ? \get_string('ambiente_prod', 'local_senceasistencia')
            : \get_string('ambiente_test', 'local_senceasistencia');
        $color_etiqueta = ($ambiente === 'prod') ? '#28a745' : '#ffc107';
        $color_texto_etiqueta = ($ambiente === 'prod') ? 'white' : 'black';

        // ==================== HTML ====================
        $html = \html_writer::start_div('sence-bloque-curso', array(
            'style' => 'max-width:600px;margin:180px auto -100px;padding:20px;'
                     . 'border:2px solid ' . $color_principal . ';'
                     . 'border-radius:8px;background:#f0f8ff;text-align:center;'
        ));

        // Título
        $html .= \html_writer::tag('h3', \get_string('pluginname', 'local_senceasistencia'), array(
            'style' => 'color:' . $color_principal . ';margin-top:10px;'
        ));

        // ==================== CRONÓMETRO (solo si hay sesión activa) ====================
        if ($sesionactiva && !$tienecierre) {
            $tiempo_transcurrido = time() - $sesionactiva->timecreated;
            $tiempo_restante = $duracion_seg - $tiempo_transcurrido;
            if ($tiempo_restante < 0) {
                $tiempo_restante = 0;
            }

            // Si la sesión tiene duración guardada, usarla (más confiable)
            if (!empty($sesionactiva->duracion) && $sesionactiva->duracion > 0) {
                $tiempo_restante = $sesionactiva->duracion - $tiempo_transcurrido;
                if ($tiempo_restante < 0) {
                    $tiempo_restante = 0;
                }
            }

            $html .= '<div id="sence-cronometro-container" style="'
                   . 'margin:15px 0;padding:15px;background:white;'
                   . 'border:2px dashed ' . $color_principal . ';'
                   . 'border-radius:8px;">';

            $html .= '<p style="margin:0 0 8px 0;font-size:13px;color:#666;'
                   . 'text-transform:uppercase;font-weight:bold;letter-spacing:1px;">'
                   . \get_string('cronometro_titulo', 'local_senceasistencia') . '</p>';

            $html .= '<div id="sence-cronometro" style="'
                   . 'font-size:42px;font-weight:bold;font-family:monospace;'
                   . 'color:' . $color_principal . ';line-height:1.1;">'
                   . '--:--:--</div>';

            $html .= '<p id="sence-cronometro-nota" style="'
                   . 'margin:8px 0 0 0;font-size:12px;color:#666;">'
                   . \get_string('cronometro_nota', 'local_senceasistencia') . '</p>';

            $html .= '</div>';

            // JavaScript del cronómetro
            $html .= '<script>
            (function() {
                var segundosRestantes = ' . (int)$tiempo_restante . ';
                var colorPrincipal = "' . $color_principal . '";
                var colorAlerta = "#dc3545";
                var colorExpirado = "#ff0000";
                var alertaMostrada = false;

                var display = document.getElementById("sence-cronometro");
                var contenedor = document.getElementById("sence-cronometro-container");
                var nota = document.getElementById("sence-cronometro-nota");

                if (!display) return;

                function formatearTiempo(seg) {
                    var h = Math.floor(seg / 3600);
                    var m = Math.floor((seg % 3600) / 60);
                    var s = seg % 60;
                    return (h < 10 ? "0" : "") + h + ":" +
                           (m < 10 ? "0" : "") + m + ":" +
                           (s < 10 ? "0" : "") + s;
                }

                function actualizar() {
                    if (segundosRestantes <= 0) {
                        display.textContent = "00:00:00";
                        display.style.color = colorExpirado;
                        display.style.animation = "sence-pulse 1s infinite";
                        if (nota) {
                            nota.innerHTML = "<strong style=\'color:#ff0000;\'>'
                                . \get_string('cronometro_expirado', 'local_senceasistencia')
                                . '</strong>";
                        }
                        if (contenedor) {
                            contenedor.style.borderColor = colorExpirado;
                            contenedor.style.background = "#ffe6e6";
                        }
                        return;
                    }

                    display.textContent = formatearTiempo(segundosRestantes);

                    // Alerta a los 10 minutos (600 segundos)
                    if (segundosRestantes <= 600 && !alertaMostrada) {
                        alertaMostrada = true;
                        display.style.color = colorAlerta;
                        if (nota) {
                            nota.innerHTML = "<strong style=\'color:#dc3545;\'>'
                                . \get_string('cronometro_alerta', 'local_senceasistencia')
                                . '</strong>";
                        }
                        if (contenedor) {
                            contenedor.style.borderColor = colorAlerta;
                            contenedor.style.background = "#fff3cd";
                        }
                    }

                    segundosRestantes--;
                    setTimeout(actualizar, 1000);
                }

                // Agregar animación CSS
                var style = document.createElement("style");
                style.textContent = "@keyframes sence-pulse { 0%,100% { opacity:1; } 50% { opacity:0.5; } }";
                document.head.appendChild(style);

                actualizar();
            })();
            </script>';
        }

        // Instrucciones
        $html .= \html_writer::tag('p', \get_string('instrucciones_claveunica', 'local_senceasistencia'), array(
            'style' => 'font-size:14px;color:#333;'
        ));

        // ==================== BOTÓN INICIO ====================
        if (!$sesionactiva || $tienecierre) {
            $html .= '<form method="post" action="' . s($url_iniciar) . '" style="margin:15px 0;">';
            $html .= '<input type="hidden" name="RutOtec" value="' . s($rutotec) . '">';
            $html .= '<input type="hidden" name="Token" value="' . s($token) . '">';
            $html .= '<input type="hidden" name="CodSence" value="' . s($codigosence) . '">';
            $html .= '<input type="hidden" name="CodigoCurso" value="' . s($course->shortname) . '">';
            $html .= '<input type="hidden" name="LineaCapacitacion" value="' . s($lineacap) . '">';
            $html .= '<input type="hidden" name="RunAlumno" value="' . s($runalumno) . '">';
            $html .= '<input type="hidden" name="IdSesionAlumno" value="' . s($idsesionalumno) . '">';
            $html .= '<input type="hidden" name="UrlRetoma" value="' . s($urlretoma_inicio) . '">';
            $html .= '<input type="hidden" name="UrlError" value="' . s($urlerror_inicio) . '">';
            $html .= '<button type="submit" style="background:' . $color_principal . ';color:white;'
                   . 'padding:14px 28px;border:none;border-radius:6px;font-size:16px;'
                   . 'font-weight:bold;cursor:pointer;">';
            $html .= \get_string('ir_sence', 'local_senceasistencia');
            $html .= '</button>';
            $html .= '</form>';
        }

        // ==================== BOTÓN CIERRE ====================
        if ($sesionactiva && !$tienecierre) {
            $html .= \html_writer::tag('p', \get_string('sence_session_activa', 'local_senceasistencia'), array(
                'style' => 'color:' . $color_principal . ';font-weight:bold;font-size:14px;'
            ));

            $html .= '<form method="post" action="' . s($url_cerrar) . '" style="margin:15px 0;">';
            $html .= '<input type="hidden" name="RutOtec" value="' . s($rutotec) . '">';
            $html .= '<input type="hidden" name="Token" value="' . s($token) . '">';
            $html .= '<input type="hidden" name="CodSence" value="' . s($codigosence) . '">';
            $html .= '<input type="hidden" name="CodigoCurso" value="' . s($course->shortname) . '">';
            $html .= '<input type="hidden" name="LineaCapacitacion" value="' . s($lineacap) . '">';
            $html .= '<input type="hidden" name="RunAlumno" value="' . s($runalumno) . '">';
            $html .= '<input type="hidden" name="IdSesionAlumno" value="' . s($sesionactiva->sence_idsessionalumno) . '">';
            $html .= '<input type="hidden" name="IdSesionSence" value="' . s($sesionactiva->sence_idsession) . '">';
            $html .= '<input type="hidden" name="UrlRetoma" value="' . s($urlretoma_cierre) . '">';
            $html .= '<input type="hidden" name="UrlError" value="' . s($urlerror_cierre) . '">';
            $html .= '<button type="submit" style="background:#dc3545;color:white;'
                   . 'padding:14px 28px;border:none;border-radius:6px;font-size:16px;'
                   . 'font-weight:bold;cursor:pointer;">';
            $html .= \get_string('ir_sence_cerrar', 'local_senceasistencia');
            $html .= '</button>';
            $html .= '</form>';
        }

        // Link a Clave Única
        $html .= \html_writer::tag('p', \html_writer::link(
            'https://claveunica.gob.cl/',
            \get_string('claveunica_link', 'local_senceasistencia'),
            array('target' => '_blank', 'style' => 'font-size:12px;color:#666;')
        ));

        $html .= \html_writer::end_div();

        return $html;
    }
}