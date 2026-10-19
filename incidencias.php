<?php
// LEER LOS DATOS JSON

$archivo_incidencias = 'datos/incidencias.json';
$archivo_victimas = 'datos/victimas.json';
$archivo_agresores = 'datos/agresores.json';

// Comprobar que existen los archivos

if (!file_exists($archivo_incidencias)) {
    file_put_contents($archivo_incidencias, '[]');
}

if (!file_exists($archivo_victimas)) {
    file_put_contents($archivo_victimas, '[]');
}

if (!file_exists($archivo_agresores)) {
    file_put_contents($archivo_agresores, '[]');
}

// Obtener los registros

$incidencias = json_decode(file_get_contents($archivo_incidencias), true);
$victimas = json_decode(file_get_contents($archivo_victimas), true);
$agresores = json_decode(file_get_contents($archivo_agresores), true);

if (!is_array($incidencias)) $incidencias = [];
if (!is_array($victimas)) $victimas = [];
if (!is_array($agresores)) $agresores = [];

$mensaje = '';

// INDICADORES DE RIESGO

$indicadores = [
    'amenazas_graves' => 'Existencia de amenazas graves',
    'violencia_previa' => 'Episodios previos de violencia',
    'aumento_violencia' => 'Aumento reciente de la frecuencia o gravedad',
    'lesiones' => 'Agresiones físicas con lesiones',
    'amenazas_muerte' => 'Amenazas de muerte',
    'acoso' => 'Acoso, persecución o control persistente',
    'incumplimiento' => 'Incumplimiento de medidas de protección',
    'armas' => 'Acceso a armas o amenazas con armas',
    'separacion' => 'Separación reciente o conflicto relacionado',
    'violencia_terceros' => 'Antecedentes de violencia hacia otras personas',
    'sustancias' => 'Consumo problemático asociado a episodios violentos',
    'menores' => 'Amenazas o violencia que afecten a menores'
];

// NAVEGACION

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['inicio'])) {
        require('index.php');
        die();
    }

    if (isset($_POST['victimas'])) {
        require('victimas.php');
        die();
    }

    if (isset($_POST['agresores'])) {
        require('agresores.php');
        die();
    }

    if (isset($_POST['valoracion'])) {
        require('valoracion.php');
        die();
    }

    if (isset($_POST['informe'])) {
        require('informe.php');
        die();
    }

    // REGISTRAR UNA INCIDENCIA

    if (isset($_POST['guardar'])) {

        $fecha = $_POST['fecha'] ?? '';
        $lugar = trim($_POST['lugar'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $id_victima = $_POST['id_victima'] ?? '';
        $id_agresor = $_POST['id_agresor'] ?? '';
        $observaciones = trim($_POST['observaciones'] ?? '');

        // Comprobar que la víctima existe

        $victima_existe = false;

        foreach ($victimas as $victima) {
            if (($victima['id'] ?? '') === $id_victima) {
                $victima_existe = true;
            }
        }

        // Comprobar que el agresor existe

        $agresor_existe = false;

        foreach ($agresores as $agresor) {
            if (($agresor['id'] ?? '') === $id_agresor) {
                $agresor_existe = true;
            }
        }

        // Comprobar los indicadores

        $respuestas = [];

        foreach ($indicadores as $clave => $nombre) {
            $respuesta = $_POST[$clave] ?? 'Desconocido';

            if (!in_array($respuesta, ['Sí', 'No', 'Desconocido'], true)) {
                $respuesta = 'Desconocido';
            }

            $respuestas[$clave] = $respuesta;
        }

        // Validar los campos obligatorios

        if ($fecha === '' || $lugar === '' || $descripcion === '') {
            $mensaje = 'Completa la fecha, el lugar y la descripción.';

        } elseif (!$victima_existe || !$agresor_existe) {
            $mensaje = 'Selecciona una víctima y un agresor registrados.';

        } else {

            // Crear el registro

            $nueva_incidencia = [
                'id' => uniqid('INC-'),
                'fecha' => $fecha,
                'lugar' => $lugar,
                'descripcion' => $descripcion,
                'id_victima' => $id_victima,
                'id_agresor' => $id_agresor,
                'indicadores' => $respuestas,
                'observaciones' => $observaciones
            ];

            $incidencias[] = $nueva_incidencia;

            // Guardar en JSON

            $resultado = file_put_contents(
                $archivo_incidencias,
                json_encode(
                    $incidencias,
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
                ),
                LOCK_EX
            );

            if ($resultado !== false) {
                $mensaje = 'Incidencia registrada correctamente.';
            } else {
                array_pop($incidencias);
                $mensaje = 'Error al guardar la incidencia.';
            }
        }
    }
}

// MOSTRAR LA VISTA

require('incidencias.php');
?>
