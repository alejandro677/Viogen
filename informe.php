<?php
// LEER LOS DATOS JSON

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

$mensaje = '';
$id_incidencia = '';
$incidencia_elegida = null;
$victima_elegida = null;
$agresor_elegido = null;
$valoracion_elegida = null;
$respuestas = [];
$detectados = [];
$pendientes = [];

// NAVEGACION

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['inicio'])) {
        require('index.php');
        die();
    }

    if (isset($_POST['incidencias'])) {
        require('incidencias.php');
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

    // GENERAR EL INFORME A PARTIR DE LOS DATOS GUARDADOS

    if (isset($_POST['generar'])) {

        $id_incidencia = $_POST['id_incidencia'] ?? '';

        // Buscar la incidencia

        foreach ($incidencias as $inc) {
            if (($inc['id'] ?? '') === $id_incidencia) {
                $incidencia_elegida = $inc;
            }
        }

        if ($incidencia_elegida === null) {
            $mensaje = 'Selecciona una incidencia registrada.';

        } else {

            // Buscar la víctima y el agresor

            foreach ($victimas as $v) {
                if (($v['id'] ?? '') === ($incidencia_elegida['id_victima'] ?? '')) {
                    $victima_elegida = $v;
                }
            }

            foreach ($agresores as $a) {
                if (($a['id'] ?? '') === ($incidencia_elegida['id_agresor'] ?? '')) {
                    $agresor_elegido = $a;
                }
            }

            // Buscar la valoración más reciente de esa incidencia
            // (se guardan en orden, así que la última que coincide es la más reciente)

            foreach ($valoraciones as $val) {
                if (($val['id_incidencia'] ?? '') === $id_incidencia) {
                    $valoracion_elegida = $val;
                }
            }

            // Respuestas: las de la valoración si existe, si no las de la incidencia

            if ($valoracion_elegida !== null) {
                $respuestas = $valoracion_elegida['respuestas'] ?? [];
            } else {
                $respuestas = $incidencia_elegida['indicadores'] ?? [];
            }

            // Factores detectados y pendientes

            foreach ($indicadores as $clave => $nombre) {
                $respuesta = $respuestas[$clave] ?? 'Desconocido';

                if ($respuesta === 'Sí') {
                    $detectados[] = $nombre;
                }

                if ($respuesta === 'Desconocido') {
                    $pendientes[] = $nombre;
                }
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
    <title>Informe final</title>
</head>

<body>

    <h1>Informe final</h1>

    <p>Uso exclusivamente académico y con datos ficticios. El informe se genera a partir de los datos guardados.</p>

    <?php if ($mensaje !== ''): ?>
        <p class="mensaje"><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>

    <?php if (count($incidencias) === 0): ?>

        <p>No hay incidencias registradas. Registra una incidencia para poder generar un informe.</p>

    <?php else: ?>

        <h2>Selecciona una incidencia</h2>

        <form method="POST">
            <select name="id_incidencia" required>
                <option value="">Seleccionar...</option>
                <?php foreach ($incidencias as $inc): ?>
                    <option value="<?= htmlspecialchars($inc['id']) ?>" <?= ($inc['id'] === $id_incidencia) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($inc['id']) ?> - <?= htmlspecialchars($inc['fecha'] ?? '') ?> - <?= htmlspecialchars($inc['lugar'] ?? '') ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button name="generar">Generar informe</button>
        </form>

    <?php endif; ?>

    <?php if ($incidencia_elegida !== null): ?>

        <hr>

        <h2>Informe de la incidencia <?= htmlspecialchars($incidencia_elegida['id']) ?></h2>

        <!-- 1. IDENTIFICACION DEL CASO -->

        <h3>1. Identificación del caso</h3>

        <ul>
            <li><strong>Código:</strong> <?= htmlspecialchars($incidencia_elegida['id']) ?></li>
            <li><strong>Fecha de los hechos:</strong> <?= htmlspecialchars($incidencia_elegida['fecha'] ?? '') ?></li>
            <li><strong>Víctima:</strong>
                <?php if ($victima_elegida !== null): ?>
                    <?= htmlspecialchars($victima_elegida['id']) ?> - <?= htmlspecialchars($victima_elegida['nombre'] ?? '') ?>
                <?php else: ?>
                    no encontrada en los registros
                <?php endif; ?>
            </li>
            <li><strong>Presunto agresor:</strong>
                <?php if ($agresor_elegido !== null): ?>
                    <?= htmlspecialchars($agresor_elegido['id']) ?> - <?= htmlspecialchars($agresor_elegido['nombre'] ?? '') ?>
                <?php else: ?>
                    no encontrado en los registros
                <?php endif; ?>
            </li>
        </ul>

        <!-- 2. RESUMEN DE LOS HECHOS -->

        <h3>2. Resumen de los hechos</h3>

        <p><strong>Lugar:</strong> <?= htmlspecialchars($incidencia_elegida['lugar'] ?? '') ?></p>
        <p><?= htmlspecialchars($incidencia_elegida['descripcion'] ?? '') ?></p>

        <!-- 3. INDICADORES DE RIESGO -->

        <h3>3. Indicadores de riesgo</h3>

        <?php if ($valoracion_elegida === null): ?>
            <p>Esta incidencia todavía no tiene valoración. Se muestran las respuestas registradas con la incidencia.</p>
        <?php endif; ?>

        <table>
            <thead>
                <tr>
                    <th>Indicador</th>
                    <th>Respuesta</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($indicadores as $clave => $nombre): ?>
                    <tr>
                        <td><?= htmlspecialchars($nombre) ?></td>
                        <td><?= htmlspecialchars($respuestas[$clave] ?? 'Desconocido') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if (($incidencia_elegida['observaciones'] ?? '') !== ''): ?>
            <p><strong>Observaciones de la incidencia:</strong> <?= htmlspecialchars($incidencia_elegida['observaciones']) ?></p>
        <?php endif; ?>

        <?php if (($valoracion_elegida['observaciones'] ?? '') !== ''): ?>
            <p><strong>Observaciones de la valoración:</strong> <?= htmlspecialchars($valoracion_elegida['observaciones']) ?></p>
        <?php endif; ?>

        <!-- 4. FACTORES DETECTADOS Y PENDIENTES -->

        <h3>4. Factores detectados y pendientes</h3>

        <p><strong>Factores detectados:</strong></p>

        <?php if (count($detectados) === 0): ?>
            <p>No se ha detectado ningún factor.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($detectados as $factor): ?>
                    <li><?= htmlspecialchars($factor) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <p><strong>Información pendiente (desconocida):</strong></p>

        <?php if (count($pendientes) === 0): ?>
            <p>No hay información pendiente.</p>
        <?php else: ?>
            <ul>
                <?php foreach ($pendientes as $pendiente): ?>
                    <li><?= htmlspecialchars($pendiente) ?></li>
                <?php endforeach; ?>
            </ul>
        <?php endif; ?>

        <!-- 5. VALORACION FINAL -->

        <h3>5. Valoración final</h3>

        <?php if ($valoracion_elegida === null): ?>
            <p>No hay valoración guardada para esta incidencia. Realízala en la sección Valoración.</p>
        <?php else: ?>
            <p class="mensaje">
                <strong>Resultado académico orientativo: <?= htmlspecialchars($valoracion_elegida['nivel'] ?? '') ?></strong><br>
                <?= htmlspecialchars($valoracion_elegida['explicacion'] ?? '') ?>
            </p>
            <p><strong>Fecha de la valoración:</strong> <?= htmlspecialchars($valoracion_elegida['fecha_valoracion'] ?? '') ?></p>
        <?php endif; ?>

        <!-- 6. OBSERVACIONES -->

        <h3>6. Observaciones</h3>

        <p><strong>Fecha de elaboración del informe:</strong> <?= date('d/m/Y H:i') ?></p>

        <p class="mensaje">
            Este informe tiene una finalidad exclusivamente académica. El resultado es una
            valoración orientativa basada en una regla didáctica propia: no reproduce el
            algoritmo oficial de VioGén ni sustituye una valoración profesional o policial.
        </p>

    <?php endif; ?>

    <h2>Navegación</h2>

    <form method="POST">
        <button name="inicio">Inicio</button>
        <button name="incidencias">Incidencias</button>
        <button name="victimas">Víctimas</button>
        <button name="agresores">Agresores</button>
        <button name="valoracion">Valoración</button>
    </form>

</body>
</html>