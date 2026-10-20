<?php
// ARCHIVOS JSON

// Crear la carpeta datos si no existe
if (!is_dir('datos')) {
    mkdir('datos');
}

$archivo_incidencias = 'datos/incidencias.json';
$archivo_victimas = 'datos/victimas.json';
$archivo_agresores = 'datos/agresores.json';
$archivo_valoraciones = 'datos/valoraciones.json';

foreach ([$archivo_incidencias, $archivo_victimas, $archivo_agresores, $archivo_valoraciones] as $archivo) {
    if (!file_exists($archivo)) {
        file_put_contents($archivo, '[]');
    }
}

$incidencias = json_decode(file_get_contents($archivo_incidencias), true);
$victimas = json_decode(file_get_contents($archivo_victimas), true);
$agresores = json_decode(file_get_contents($archivo_agresores), true);
$valoraciones = json_decode(file_get_contents($archivo_valoraciones), true);

if (!is_array($incidencias)) $incidencias = [];
if (!is_array($victimas)) $victimas = [];
if (!is_array($agresores)) $agresores = [];
if (!is_array($valoraciones)) $valoraciones = [];

// INDICADORES 

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

$mensaje = '';
$id_incidencia = '';
$incidencia_elegida = null;
$victima_elegida = null;
$agresor_elegido = null;
$valores = [];
$valoracion_guardada = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // NAVEGACION

    if (isset($_POST['inicio'])) { require('index.php'); die(); }
    if (isset($_POST['incidencias'])) { require('incidencias.php'); die(); }
    if (isset($_POST['victimas'])) { require('victimas.php'); die(); }
    if (isset($_POST['agresores'])) { require('agresores.php'); die(); }
    if (isset($_POST['informe'])) { require('informe.php'); die(); }

    // CONSULTAR LA INCIDENCIA ELEGIDA Y SUS DATOS RELACIONADOS

    if (isset($_POST['consultar']) || isset($_POST['guardar'])) {

        $id_incidencia = $_POST['id_incidencia'] ?? '';

        foreach ($incidencias as $incidencia) {
            if (($incidencia['id'] ?? '') === $id_incidencia) {
                $incidencia_elegida = $incidencia;
            }
        }

        if ($incidencia_elegida === null) {
            $mensaje = 'Selecciona una incidencia registrada.';

        } else {

            // Buscar la víctima y el agresor de la incidencia

            foreach ($victimas as $victima) {
                if (($victima['id'] ?? '') === ($incidencia_elegida['id_victima'] ?? '')) {
                    $victima_elegida = $victima;
                }
            }

            foreach ($agresores as $agresor) {
                if (($agresor['id'] ?? '') === ($incidencia_elegida['id_agresor'] ?? '')) {
                    $agresor_elegido = $agresor;
                }
            }

            // Las respuestas de partida son las de la incidencia

            $valores = $incidencia_elegida['indicadores'] ?? [];
        }
    }

    // GUARDAR LA VALORACION

    if (isset($_POST['guardar']) && $incidencia_elegida !== null) {

        $respuestas = [];
        $detectados = [];
        $pendientes = [];

        foreach ($indicadores as $clave => $nombre) {
            $respuesta = $_POST[$clave] ?? 'Desconocido';

            if (!in_array($respuesta, ['Sí', 'No', 'Desconocido'], true)) {
                $respuesta = 'Desconocido';
            }

            $respuestas[$clave] = $respuesta;

            if ($respuesta === 'Sí') {
                $detectados[] = $nombre;
            }

            if ($respuesta === 'Desconocido') {
                $pendientes[] = $nombre;
            }
        }

        // El formulario conserva las respuestas elegidas

        $valores = $respuestas;

        // se cuentan los indicadores respondidos con "Sí"

        $total = count($detectados);

        if ($total <= 2) {
            $nivel = 'Bajo';
        } elseif ($total <= 5) {
            $nivel = 'Medio';
        } elseif ($total <= 8) {
            $nivel = 'Alto';
        } else {
            $nivel = 'Muy alto';
        }

        $explicacion = 'Se han detectado ' . $total . ' de ' . count($indicadores)
            . ' indicadores y ' . count($pendientes) . ' están pendientes de confirmar. '
            . 'Regla didáctica: 0-2 Bajo, 3-5 Medio, 6-8 Alto, 9-12 Muy alto.';

        $valoracion = [
            'id' => uniqid('VAL-'),
            'id_incidencia' => $id_incidencia,
            'fecha_valoracion' => date('Y-m-d H:i:s'),
            'respuestas' => $respuestas,
            'detectados' => $detectados,
            'pendientes' => $pendientes,
            'total_detectados' => count($detectados),
            'total_pendientes' => count($pendientes),
            'nivel' => $nivel,
            'explicacion' => $explicacion,
            'observaciones' => trim($_POST['observaciones'] ?? '')
        ];

        $valoraciones[] = $valoracion;

        // Guardar en JSON

        $resultado = file_put_contents(
            $archivo_valoraciones,
            json_encode($valoraciones, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
            LOCK_EX
        );

        if ($resultado !== false) {
            $mensaje = 'Valoración académica guardada. Puedes consultarla en el informe.';
            $valoracion_guardada = $valoracion;
        } else {
            array_pop($valoraciones);
            $mensaje = 'Error al guardar la valoración.';
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
    <title>Valoración orientativa</title>
</head>
<body>

    <h1>Valoración orientativa</h1>

    <?php if ($mensaje !== ''): ?>
        <p class="mensaje"><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>

    <?php if (count($incidencias) === 0): ?>

        <p>No hay incidencias registradas. Registra una incidencia antes de realizar una valoración.</p>

    <?php else: ?>

        <!-- PASO 1: ELEGIR LA INCIDENCIA -->

        <h2>1. Selecciona una incidencia</h2>

        <form method="POST">
            <select name="id_incidencia" required>
                <option value="">Seleccionar...</option>
                <?php foreach ($incidencias as $incidencia): ?>
                    <option value="<?= htmlspecialchars($incidencia['id']) ?>" <?= ($incidencia['id'] === $id_incidencia) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($incidencia['id']) ?> - <?= htmlspecialchars($incidencia['fecha'] ?? '') ?> - <?= htmlspecialchars($incidencia['lugar'] ?? '') ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button name="consultar">Consultar incidencia</button>
        </form>

        <?php if ($incidencia_elegida !== null): ?>

            <!-- INFORMACION RELACIONADA -->

            <h2>Información de la incidencia</h2>

            <ul>
                <li><strong>Código:</strong> <?= htmlspecialchars($incidencia_elegida['id']) ?></li>
                <li><strong>Fecha:</strong> <?= htmlspecialchars($incidencia_elegida['fecha'] ?? '') ?></li>
                <li><strong>Lugar:</strong> <?= htmlspecialchars($incidencia_elegida['lugar'] ?? '') ?></li>
                <li><strong>Descripción:</strong> <?= htmlspecialchars($incidencia_elegida['descripcion'] ?? '') ?></li>
                <li><strong>Víctima:</strong>
                    <?php if ($victima_elegida !== null): ?>
                        <?= htmlspecialchars($victima_elegida['id']) ?> - <?= htmlspecialchars($victima_elegida['nombre'] ?? '') ?>
                    <?php else: ?>
                        no encontrada en los registros
                    <?php endif; ?>
                </li>
                <li><strong>agresor:</strong>
                    <?php if ($agresor_elegido !== null): ?>
                        <?= htmlspecialchars($agresor_elegido['id']) ?> - <?= htmlspecialchars($agresor_elegido['nombre'] ?? '') ?>
                    <?php else: ?>
                        no encontrado en los registros
                    <?php endif; ?>
                </li>
                <li><strong>Observaciones:</strong> <?= htmlspecialchars($incidencia_elegida['observaciones'] ?? '') ?></li>
            </ul>

            <!-- PASO 2: INDICADORES -->

            <h2>2. Indicadores de riesgo</h2>

            <form method="POST">

                <input type="hidden" name="id_incidencia" value="<?= htmlspecialchars($id_incidencia) ?>">

                <?php foreach ($indicadores as $clave => $nombre): ?>
                    <label><?= htmlspecialchars($nombre) ?></label>
                    <select name="<?= htmlspecialchars($clave) ?>">
                        <?php foreach (['Sí', 'No', 'Desconocido'] as $opcion): ?>
                            <option value="<?= $opcion ?>" <?= (($valores[$clave] ?? 'Desconocido') === $opcion) ? 'selected' : '' ?>>
                                <?= $opcion ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                <?php endforeach; ?>

                <label>Observaciones</label>
                <textarea name="observaciones"></textarea>

                <button name="guardar">Guardar valoración</button>

            </form>

        <?php endif; ?>

        <!-- RESULTADO -->

        <?php if ($valoracion_guardada !== null): ?>

            <h2>Resultado</h2>

            <p class="mensaje">
                <strong>Nivel: <?= htmlspecialchars($valoracion_guardada['nivel']) ?></strong><br>
                <?= htmlspecialchars($valoracion_guardada['explicacion']) ?>
            </p>

            <h3>Factores detectados</h3>

            <?php if (count($valoracion_guardada['detectados']) === 0): ?>
                <p>No se ha detectado ningún factor.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($valoracion_guardada['detectados'] as $factor): ?>
                        <li><?= htmlspecialchars($factor) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

            <h3>Información pendiente</h3>

            <?php if (count($valoracion_guardada['pendientes']) === 0): ?>
                <p>No hay información pendiente.</p>
            <?php else: ?>
                <ul>
                    <?php foreach ($valoracion_guardada['pendientes'] as $pendiente): ?>
                        <li><?= htmlspecialchars($pendiente) ?></li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>

        <?php endif; ?>

    <?php endif; ?>

    <h2>Navegación</h2>

    <form method="POST">
        <button name="inicio">Inicio</button>
        <button name="incidencias">Incidencias</button>
        <button name="victimas">Víctimas</button>
        <button name="agresores">Agresores</button>
        <button name="informe">Informe</button>
    </form>

</body>
</html>