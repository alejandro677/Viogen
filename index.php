<?php
// CONTROLADOR UNICO: recibe todos los formularios, guarda los datos y elige la vista

ini_set('display_errors', '1');
ini_set('error_reporting', (string) E_ALL);

require_once 'funciones.php';
require_once 'victimas.php';
require_once 'agresores.php';

// DATOS PERSISTENTES (archivos JSON)

$lista_victimas = leer_archivo_json(RUTA_ARCHIVO_VICTIMAS);
$lista_agresores = leer_archivo_json(RUTA_ARCHIVO_AGRESORES);
$lista_incidencias = leer_archivo_json(RUTA_ARCHIVO_INCIDENCIAS);
$lista_valoraciones = leer_archivo_json(RUTA_ARCHIVO_VALORACIONES);

// DATOS TEMPORALES (campo oculto con JSON)

$datos_formularios = crear_datos_formularios_vacios();

if (isset($_POST['datos']) && is_string($_POST['datos'])) {
    $datos_formularios = recuperar_datos_formularios($_POST['datos']);
}

$mensaje_usuario = '';
$valoracion_recien_guardada = null;

// PAGINA DE ORIGEN Y PAGINA A MOSTRAR

$paginas_permitidas = ['inicio', 'victimas', 'agresores', 'incidencias', 'valoracion', 'informe'];

$pagina_origen = $_POST['pagina'] ?? '';

if (!is_string($pagina_origen) || !in_array($pagina_origen, $paginas_permitidas, true)) {
    $pagina_origen = '';
}

// Por defecto se vuelve a mostrar la página desde la que se envió el formulario

$pagina_a_mostrar = ($pagina_origen !== '') ? $pagina_origen : 'inicio';

// ACTUALIZAR LOS DATOS TEMPORALES CON EL FORMULARIO RECIBIDO

if ($pagina_origen === 'victimas') {
    $datos_formularios['victima'] = [
        'id' => obtener_texto_enviado('id'),
        'nombre' => obtener_texto_enviado('nombre'),
        'edad' => obtener_texto_enviado('edad'),
        'indicadores' => filtrar_respuestas_validas(INDICADORES_VICTIMA, $_POST['indicadores'] ?? []),
        'observaciones' => obtener_texto_enviado('observaciones')
    ];
}

if ($pagina_origen === 'agresores') {
    $datos_formularios['agresor'] = [
        'id' => obtener_texto_enviado('id'),
        'nombre' => obtener_texto_enviado('nombre'),
        'antecedentes' => obtener_texto_enviado('antecedentes'),
        'indicadores' => filtrar_respuestas_validas(INDICADORES_AGRESOR, $_POST['indicadores'] ?? []),
        'observaciones' => obtener_texto_enviado('observaciones')
    ];
}

if ($pagina_origen === 'incidencias') {
    $datos_formularios['incidencia'] = [
        'fecha' => obtener_texto_enviado('fecha'),
        'lugar' => obtener_texto_enviado('lugar'),
        'descripcion' => obtener_texto_enviado('descripcion'),
        'id_victima' => obtener_texto_enviado('id_victima'),
        'id_agresor' => obtener_texto_enviado('id_agresor'),
        'indicadores' => filtrar_respuestas_validas(INDICADORES_INCIDENCIA, $_POST['indicadores'] ?? []),
        'observaciones' => obtener_texto_enviado('observaciones')
    ];
}

if ($pagina_origen === 'valoracion') {
    $datos_formularios['valoracion']['observaciones'] = obtener_texto_enviado('observaciones');

    // La incidencia elegida solo cambia al pulsar "Consultar"

    if (isset($_POST['consultar'])) {
        $datos_formularios['valoracion']['id_incidencia'] = obtener_texto_enviado('id_incidencia');
    } else {
        $datos_formularios['valoracion']['indicadores'] = filtrar_respuestas_validas(INDICADORES_INCIDENCIA, $_POST['indicadores'] ?? []);
    }
}

if ($pagina_origen === 'informe' && isset($_POST['generar'])) {
    $datos_formularios['informe']['id_incidencia'] = obtener_texto_enviado('id_incidencia');
}

// NAVEGACION (el botón pulsado tiene el nombre de la página de destino)

foreach ($paginas_permitidas as $pagina_destino) {
    if (isset($_POST[$pagina_destino])) {
        $pagina_a_mostrar = $pagina_destino;
    }
}

// GESTION DE VICTIMAS Y AGRESORES

if ($pagina_origen === 'victimas') {
    gestionar_formulario_victimas($lista_victimas, $datos_formularios, $mensaje_usuario);
}

if ($pagina_origen === 'agresores') {
    gestionar_formulario_agresores($lista_agresores, $datos_formularios, $mensaje_usuario);
}

// ACCIONES: INCIDENCIAS

if ($pagina_origen === 'incidencias' && isset($_POST['guardar'])) {

    $datos_incidencia = $datos_formularios['incidencia'];

    if ($datos_incidencia['fecha'] === '' || $datos_incidencia['lugar'] === '' || $datos_incidencia['descripcion'] === '') {
        $mensaje_usuario = 'Completa la fecha, el lugar y la descripción.';

    } elseif (buscar_registro_por_id($lista_victimas, $datos_incidencia['id_victima']) === null
        || buscar_registro_por_id($lista_agresores, $datos_incidencia['id_agresor']) === null) {
        $mensaje_usuario = 'Selecciona una víctima y un agresor registrados.';

    } else {

        // Crear el registro

        $lista_incidencias[] = [
            'id' => uniqid('INC-'),
            'fecha' => $datos_incidencia['fecha'],
            'lugar' => $datos_incidencia['lugar'],
            'descripcion' => $datos_incidencia['descripcion'],
            'id_victima' => $datos_incidencia['id_victima'],
            'id_agresor' => $datos_incidencia['id_agresor'],
            'indicadores' => $datos_incidencia['indicadores'],
            'observaciones' => $datos_incidencia['observaciones']
        ];

        // Guardar en JSON

        if (guardar_archivo_json(RUTA_ARCHIVO_INCIDENCIAS, $lista_incidencias)) {
            $mensaje_usuario = 'Incidencia registrada correctamente.';
            $datos_formularios['incidencia'] = crear_datos_formularios_vacios()['incidencia'];
        } else {
            $lista_incidencias = leer_archivo_json(RUTA_ARCHIVO_INCIDENCIAS);
            $mensaje_usuario = 'Error al guardar la incidencia.';
        }
    }
}

// ACCIONES: VALORACION

if ($pagina_origen === 'valoracion') {

    // Consultar una incidencia: las respuestas de partida son las de la incidencia

    if (isset($_POST['consultar'])) {
        $incidencia_consultada = buscar_registro_por_id($lista_incidencias, $datos_formularios['valoracion']['id_incidencia']);

        if ($incidencia_consultada === null) {
            $mensaje_usuario = 'Selecciona una incidencia registrada.';
        } else {
            $datos_formularios['valoracion']['indicadores'] = filtrar_respuestas_validas(INDICADORES_INCIDENCIA, $incidencia_consultada['indicadores'] ?? []);
        }
    }

    // Guardar la valoración

    if (isset($_POST['guardar'])) {
        $incidencia_consultada = buscar_registro_por_id($lista_incidencias, $datos_formularios['valoracion']['id_incidencia']);

        if ($incidencia_consultada === null) {
            $mensaje_usuario = 'Selecciona una incidencia registrada.';

        } else {

            $respuestas_indicadores = $datos_formularios['valoracion']['indicadores'];
            [$indicadores_detectados, $indicadores_pendientes] = separar_indicadores_detectados_y_pendientes(INDICADORES_INCIDENCIA, $respuestas_indicadores);

            // RESULTADO ACADÉMICO ORIENTATIVO:
            // Se emplea una regla didáctica propia para la práctica.
            // No reproduce el algoritmo oficial de VioGén ni el protocolo VPER.
            // Se cuentan los indicadores respondidos con "Sí".

            $total_indicadores_detectados = count($indicadores_detectados);
            $nivel_riesgo_orientativo = calcular_nivel_riesgo_orientativo($total_indicadores_detectados);

            $explicacion_nivel = 'Se han detectado ' . $total_indicadores_detectados . ' de ' . count(INDICADORES_INCIDENCIA)
                . ' indicadores y ' . count($indicadores_pendientes) . ' están pendientes de confirmar. '
                . 'Regla didáctica: 0-2 Bajo, 3-5 Medio, 6-8 Alto, 9-12 Muy alto.';

            $nueva_valoracion = [
                'id' => uniqid('VAL-'),
                'id_incidencia' => $incidencia_consultada['id'],
                'fecha_valoracion' => date('Y-m-d H:i:s'),
                'respuestas' => $respuestas_indicadores,
                'detectados' => $indicadores_detectados,
                'pendientes' => $indicadores_pendientes,
                'total_detectados' => count($indicadores_detectados),
                'total_pendientes' => count($indicadores_pendientes),
                'nivel' => $nivel_riesgo_orientativo,
                'explicacion' => $explicacion_nivel,
                'observaciones' => $datos_formularios['valoracion']['observaciones']
            ];

            $lista_valoraciones[] = $nueva_valoracion;

            // Guardar en JSON

            if (guardar_archivo_json(RUTA_ARCHIVO_VALORACIONES, $lista_valoraciones)) {
                $mensaje_usuario = 'Valoración académica guardada. Puedes consultarla en el informe.';
                $valoracion_recien_guardada = $nueva_valoracion;
                $datos_formularios['valoracion']['observaciones'] = '';
            } else {
                $lista_valoraciones = leer_archivo_json(RUTA_ARCHIVO_VALORACIONES);
                $mensaje_usuario = 'Error al guardar la valoración.';
            }
        }
    }
}

// ACCIONES: INFORME

if ($pagina_origen === 'informe' && isset($_POST['generar'])) {
    if (buscar_registro_por_id($lista_incidencias, $datos_formularios['informe']['id_incidencia']) === null) {
        $mensaje_usuario = 'Selecciona una incidencia registrada.';
    }
}

// DATOS RELACIONADOS PARA LAS VISTAS DE VALORACION E INFORME

$incidencia_a_valorar = null;
$victima_a_valorar = null;
$agresor_a_valorar = null;
$datos_informe = null;

if ($pagina_a_mostrar === 'valoracion') {
    $incidencia_a_valorar = buscar_registro_por_id($lista_incidencias, $datos_formularios['valoracion']['id_incidencia']);

    if ($incidencia_a_valorar !== null) {
        $victima_a_valorar = buscar_registro_por_id($lista_victimas, $incidencia_a_valorar['id_victima'] ?? '');
        $agresor_a_valorar = buscar_registro_por_id($lista_agresores, $incidencia_a_valorar['id_agresor'] ?? '');
    }
}

if ($pagina_a_mostrar === 'informe') {
    $incidencia_del_informe = buscar_registro_por_id($lista_incidencias, $datos_formularios['informe']['id_incidencia']);

    if ($incidencia_del_informe !== null) {

        // La valoración más reciente de la incidencia (se guardan en orden)

        $valoracion_mas_reciente = null;

        foreach ($lista_valoraciones as $valoracion_registrada) {
            if (($valoracion_registrada['id_incidencia'] ?? '') === $incidencia_del_informe['id']) {
                $valoracion_mas_reciente = $valoracion_registrada;
            }
        }

        // Respuestas: las de la valoración si existe, si no las de la incidencia

        $respuestas_indicadores = $valoracion_mas_reciente['respuestas'] ?? ($incidencia_del_informe['indicadores'] ?? []);
        [$indicadores_detectados, $indicadores_pendientes] = separar_indicadores_detectados_y_pendientes(INDICADORES_INCIDENCIA, $respuestas_indicadores);

        $datos_informe = [
            'incidencia' => $incidencia_del_informe,
            'victima' => buscar_registro_por_id($lista_victimas, $incidencia_del_informe['id_victima'] ?? ''),
            'agresor' => buscar_registro_por_id($lista_agresores, $incidencia_del_informe['id_agresor'] ?? ''),
            'valoracion' => $valoracion_mas_reciente,
            'respuestas_indicadores' => $respuestas_indicadores,
            'indicadores_detectados' => $indicadores_detectados,
            'indicadores_pendientes' => $indicadores_pendientes,
            'fecha_elaboracion' => date('d/m/Y H:i')
        ];
    }
}

// MOSTRAR LA VISTA

require "html/$pagina_a_mostrar.html";
