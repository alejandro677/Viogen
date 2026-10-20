<?php
// LEER LOS DATOS JSON

// Crear la carpeta datos si no existe

if (!is_dir('datos')) {
    mkdir('datos');
}

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

// DATOS DEL FORMULARIO (vacíos por defecto)

$fecha = '';
$lugar = '';
$descripcion = '';
$id_victima = '';
$id_agresor = '';
$observaciones = '';
$valores = [];

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

        foreach ($indicadores as $clave => $texto) {
            $respuesta = $_POST[$clave] ?? 'Desconocido';

            if (!in_array($respuesta, ['Sí', 'No', 'Desconocido'], true)) {
                $respuesta = 'Desconocido';
            }

            $respuestas[$clave] = $respuesta;
        }

        // Si hay un error, el formulario conserva las respuestas

        $valores = $respuestas;

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

                // Vaciar el formulario
                $fecha = '';
                $lugar = '';
                $descripcion = '';
                $id_victima = '';
                $id_agresor = '';
                $observaciones = '';
                $valores = [];
            } else {
                array_pop($incidencias);
                $mensaje = 'Error al guardar la incidencia.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="css/estilos.css">
    <title>Gestión de incidencias</title>
</head>

<body>

    <h1>Gestión de incidencias</h1>

    <p>Registra únicamente datos ficticios. Los indicadores son orientativos y no sustituyen una valoración profesional.</p>

    <?php if ($mensaje !== ''): ?>
        <p class="mensaje"><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>

    <h2>Registrar incidencia</h2>

    <?php if (count($victimas) === 0 || count($agresores) === 0): ?>

        <p>Para registrar una incidencia necesitas tener al menos una víctima y un agresor registrados.</p>

    <?php else: ?>

        <form method="POST">

            <label>Fecha de los hechos</label>
            <input type="date" name="fecha" value="<?= htmlspecialchars($fecha) ?>">

            <label>Lugar de los hechos</label>
            <input type="text" name="lugar" value="<?= htmlspecialchars($lugar) ?>">

            <label>Descripción de lo ocurrido</label>
            <textarea name="descripcion"><?= htmlspecialchars($descripcion) ?></textarea>

            <label>Víctima</label>
            <select name="id_victima">
                <option value="">Seleccionar...</option>
                <?php foreach ($victimas as $v): ?>
                    <option value="<?= htmlspecialchars($v['id']) ?>" <?= ($v['id'] === $id_victima) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($v['id']) ?> - <?= htmlspecialchars($v['nombre'] ?? '') ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label>Presunto agresor</label>
            <select name="id_agresor">
                <option value="">Seleccionar...</option>
                <?php foreach ($agresores as $a): ?>
                    <option value="<?= htmlspecialchars($a['id']) ?>" <?= ($a['id'] === $id_agresor) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($a['id']) ?> - <?= htmlspecialchars($a['nombre'] ?? '') ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <h3>Indicadores de riesgo</h3>

            <?php foreach ($indicadores as $clave => $texto): ?>
                <label><?= htmlspecialchars($texto) ?></label>
                <select name="<?= htmlspecialchars($clave) ?>">
                    <?php foreach (['Sí', 'No', 'Desconocido'] as $opcion): ?>
                        <option value="<?= $opcion ?>" <?= (($valores[$clave] ?? 'Desconocido') === $opcion) ? 'selected' : '' ?>>
                            <?= $opcion ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            <?php endforeach; ?>

            <label>Observaciones</label>
            <textarea name="observaciones"><?= htmlspecialchars($observaciones) ?></textarea>

            <button name="guardar">Registrar incidencia</button>

        </form>

    <?php endif; ?>

    <h2>Incidencias registradas</h2>

    <?php if (count($incidencias) === 0): ?>
        <p>No hay incidencias registradas todavía.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Fecha</th>
                    <th>Lugar</th>
                    <th>Víctima</th>
                    <th>Agresor</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($incidencias as $inc): ?>
                    <tr>
                        <td><?= htmlspecialchars($inc['id']) ?></td>
                        <td><?= htmlspecialchars($inc['fecha'] ?? '') ?></td>
                        <td><?= htmlspecialchars($inc['lugar'] ?? '') ?></td>
                        <td><?= htmlspecialchars($inc['id_victima'] ?? '') ?></td>
                        <td><?= htmlspecialchars($inc['id_agresor'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <h2>Navegación</h2>

    <form method="POST">
        <button name="inicio">Inicio</button>
        <button name="victimas">Víctimas</button>
        <button name="agresores">Agresores</button>
        <button name="valoracion">Valoración</button>
        <button name="informe">Informe</button>
    </form>

</body>
</html>