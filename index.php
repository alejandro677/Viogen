<?php
// CONTROLADOR UNICO: recibe todos los formularios, guarda los datos y elige la vista

ini_set('display_errors', '1');
ini_set('error_reporting', (string) E_ALL);

require_once('funciones.php');
require_once('victimas.php');
require_once('agresores.php');

// DATOS PERSISTENTES (archivos JSON)

$victimas = leer_json(ARCHIVO_VICTIMAS);
$agresores = leer_json(ARCHIVO_AGRESORES);
$incidencias = leer_json(ARCHIVO_INCIDENCIAS);
$valoraciones = leer_json(ARCHIVO_VALORACIONES);

// DATOS TEMPORALES (campo oculto con JSON)

$datos = datos_vacios();

if (isset($_POST['datos']) && is_string($_POST['datos'])) {
    $datos = recuperar_datos($_POST['datos']);
}

$mensaje = '';
$valoracion_guardada = null;

// PAGINA DE ORIGEN Y PAGINA A MOSTRAR

$paginas = ['inicio', 'victimas', 'agresores', 'incidencias', 'valoracion', 'informe'];

$origen = $_POST['pagina'] ?? '';

if (!is_string($origen) || !in_array($origen, $paginas, true)) {
    $origen = '';
}

// Por defecto se vuelve a mostrar la página desde la que se envió el formulario

$pagina = ($origen !== '') ? $origen : 'inicio';

// ACTUALIZAR LOS DATOS TEMPORALES CON EL FORMULARIO RECIBIDO

if ($origen === 'victimas') {
    $datos['victima'] = [
        'id' => post_texto('id'),
        'nombre' => post_texto('nombre'),
        'edad' => post_texto('edad'),
        'indicadores' => respuestas_validas(INDICADORES_VICTIMA, $_POST['indicadores'] ?? []),
        'observaciones' => post_texto('observaciones')
    ];
}

if ($origen === 'agresores') {
    $datos['agresor'] = [
        'id' => post_texto('id'),
        'nombre' => post_texto('nombre'),
        'antecedentes' => post_texto('antecedentes'),
        'indicadores' => respuestas_validas(INDICADORES_AGRESOR, $_POST['indicadores'] ?? []),
        'observaciones' => post_texto('observaciones')
    ];
}

if ($origen === 'incidencias') {
    $datos['incidencia'] = [
        'fecha' => post_texto('fecha'),
        'lugar' => post_texto('lugar'),
        'descripcion' => post_texto('descripcion'),
        'id_victima' => post_texto('id_victima'),
        'id_agresor' => post_texto('id_agresor'),
        'indicadores' => respuestas_validas(INDICADORES, $_POST['indicadores'] ?? []),
        'observaciones' => post_texto('observaciones')
    ];
}

if ($origen === 'valoracion') {
    $datos['valoracion']['observaciones'] = post_texto('observaciones');

    // La incidencia elegida solo cambia al pulsar "Consultar"

    if (isset($_POST['consultar'])) {
        $datos['valoracion']['id_incidencia'] = post_texto('id_incidencia');
    } else {
        $datos['valoracion']['indicadores'] = respuestas_validas(INDICADORES, $_POST['indicadores'] ?? []);
    }
}

if ($origen === 'informe' && isset($_POST['generar'])) {
    $datos['informe']['id_incidencia'] = post_texto('id_incidencia');
}

// NAVEGACION (el botón pulsado tiene el nombre de la página de destino)

foreach ($paginas as $destino) {
    if (isset($_POST[$destino])) {
        $pagina = $destino;
    }
}

// GESTION DE VICTIMAS Y AGRESORES

if ($origen === 'victimas') {
    gestionar_victimas($victimas, $datos, $mensaje);
}

if ($origen === 'agresores') {
    gestionar_agresores($agresores, $datos, $mensaje);
}

// ACCIONES: INCIDENCIAS

if ($origen === 'incidencias' && isset($_POST['guardar'])) {

    $incidencia = $datos['incidencia'];

    if ($incidencia['fecha'] === '' || $incidencia['lugar'] === '' || $incidencia['descripcion'] === '') {
        $mensaje = 'Completa la fecha, el lugar y la descripción.';

    } elseif (buscar_registro($victimas, $incidencia['id_victima']) === null
        || buscar_registro($agresores, $incidencia['id_agresor']) === null) {
        $mensaje = 'Selecciona una víctima y un agresor registrados.';

    } else {

        // Crear el registro

        $incidencias[] = [
            'id' => uniqid('INC-'),
            'fecha' => $incidencia['fecha'],
            'lugar' => $incidencia['lugar'],
            'descripcion' => $incidencia['descripcion'],
            'id_victima' => $incidencia['id_victima'],
            'id_agresor' => $incidencia['id_agresor'],
            'indicadores' => $incidencia['indicadores'],
            'observaciones' => $incidencia['observaciones']
        ];

        // Guardar en JSON

        if (guardar_json(ARCHIVO_INCIDENCIAS, $incidencias)) {
            $mensaje = 'Incidencia registrada correctamente.';
            $datos['incidencia'] = datos_vacios()['incidencia'];
        } else {
            $incidencias = leer_json(ARCHIVO_INCIDENCIAS);
            $mensaje = 'Error al guardar la incidencia.';
        }
    }
}

// ACCIONES: VALORACION

if ($origen === 'valoracion') {

    // Consultar una incidencia: las respuestas de partida son las de la incidencia

    if (isset($_POST['consultar'])) {
        $incidencia = buscar_registro($incidencias, $datos['valoracion']['id_incidencia']);

        if ($incidencia === null) {
            $mensaje = 'Selecciona una incidencia registrada.';
        } else {
            $datos['valoracion']['indicadores'] = respuestas_validas(INDICADORES, $incidencia['indicadores'] ?? []);
        }
    }

    // Guardar la valoración

    if (isset($_POST['guardar'])) {
        $incidencia = buscar_registro($incidencias, $datos['valoracion']['id_incidencia']);

        if ($incidencia === null) {
            $mensaje = 'Selecciona una incidencia registrada.';

        } else {

            $respuestas = $datos['valoracion']['indicadores'];
            [$detectados, $pendientes] = clasificar_respuestas(INDICADORES, $respuestas);

            // RESULTADO ACADÉMICO ORIENTATIVO:
            // Se emplea una regla didáctica propia para la práctica.
            // No reproduce el algoritmo oficial de VioGén ni el protocolo VPER.
            // Se cuentan los indicadores respondidos con "Sí".

            $total = count($detectados);
            $nivel = nivel_riesgo($total);

            $explicacion = 'Se han detectado ' . $total . ' de ' . count(INDICADORES)
                . ' indicadores y ' . count($pendientes) . ' están pendientes de confirmar. '
                . 'Regla didáctica: 0-2 Bajo, 3-5 Medio, 6-8 Alto, 9-12 Muy alto.';

            $valoracion = [
                'id' => uniqid('VAL-'),
                'id_incidencia' => $incidencia['id'],
                'fecha_valoracion' => date('Y-m-d H:i:s'),
                'respuestas' => $respuestas,
                'detectados' => $detectados,
                'pendientes' => $pendientes,
                'total_detectados' => count($detectados),
                'total_pendientes' => count($pendientes),
                'nivel' => $nivel,
                'explicacion' => $explicacion,
                'observaciones' => $datos['valoracion']['observaciones']
            ];

            $valoraciones[] = $valoracion;

            // Guardar en JSON

            if (guardar_json(ARCHIVO_VALORACIONES, $valoraciones)) {
                $mensaje = 'Valoración académica guardada. Puedes consultarla en el informe.';
                $valoracion_guardada = $valoracion;
                $datos['valoracion']['observaciones'] = '';
            } else {
                $valoraciones = leer_json(ARCHIVO_VALORACIONES);
                $mensaje = 'Error al guardar la valoración.';
            }
        }
    }
}

// ACCIONES: INFORME

if ($origen === 'informe' && isset($_POST['generar'])) {
    if (buscar_registro($incidencias, $datos['informe']['id_incidencia']) === null) {
        $mensaje = 'Selecciona una incidencia registrada.';
    }
}

// DATOS RELACIONADOS PARA LAS VISTAS DE VALORACION E INFORME

$incidencia_valoracion = null;
$victima_valoracion = null;
$agresor_valoracion = null;
$informe = null;

if ($pagina === 'valoracion') {
    $incidencia_valoracion = buscar_registro($incidencias, $datos['valoracion']['id_incidencia']);

    if ($incidencia_valoracion !== null) {
        $victima_valoracion = buscar_registro($victimas, $incidencia_valoracion['id_victima'] ?? '');
        $agresor_valoracion = buscar_registro($agresores, $incidencia_valoracion['id_agresor'] ?? '');
    }
}

if ($pagina === 'informe') {
    $incidencia = buscar_registro($incidencias, $datos['informe']['id_incidencia']);

    if ($incidencia !== null) {

        // La valoración más reciente de la incidencia (se guardan en orden)

        $valoracion_informe = null;

        foreach ($valoraciones as $valoracion) {
            if (($valoracion['id_incidencia'] ?? '') === $incidencia['id']) {
                $valoracion_informe = $valoracion;
            }
        }

        // Respuestas: las de la valoración si existe, si no las de la incidencia

        $respuestas = $valoracion_informe['respuestas'] ?? ($incidencia['indicadores'] ?? []);
        [$detectados, $pendientes] = clasificar_respuestas(INDICADORES, $respuestas);

        $informe = [
            'incidencia' => $incidencia,
            'victima' => buscar_registro($victimas, $incidencia['id_victima'] ?? ''),
            'agresor' => buscar_registro($agresores, $incidencia['id_agresor'] ?? ''),
            'valoracion' => $valoracion_informe,
            'respuestas' => $respuestas,
            'detectados' => $detectados,
            'pendientes' => $pendientes,
            'fecha_elaboracion' => date('d/m/Y H:i')
        ];
    }
}

// MOSTRAR LA VISTA

require __DIR__ . '/html/' . $pagina . '.html';
