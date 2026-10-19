<?php
// LEER LOS DATOS JSON

$archivo = 'datos/agresores.json';

// Comprobar que existe el archivo

if (!file_exists($archivo)) {
    file_put_contents($archivo, '[]');
}

// Obtener los registros

$agresores = json_decode(file_get_contents($archivo), true);

if (!is_array($agresores)) $agresores = [];

$mensaje = '';

// INDICADORES DE RIESGO

$indicadores = [
    'violencia_previa' => 'Episodios previos de violencia',
    'amenazas_graves' => 'Amenazas graves',
    'armas' => 'Acceso a armas o amenazas con armas',
    'incumplimiento' => 'Incumplimiento de medidas de protección',
    'acoso' => 'Acoso, persecución o control persistente',
    'sustancias' => 'Consumo problemático de alcohol u otras sustancias'
];

// DATOS DEL FORMULARIO (vacíos por defecto)

$agresor = [
    'id' => '',
    'nombre' => '',
    'antecedentes' => '',
    'indicadores' => [],
    'observaciones' => ''
];

$editando = false;

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

    if (isset($_POST['valoracion'])) {
        require('valoracion.php');
        die();
    }

    if (isset($_POST['informe'])) {
        require('informe.php');
        die();
    }

    // CARGAR UN AGRESOR EN EL FORMULARIO PARA MODIFICARLO

    if (isset($_POST['editar'])) {

        foreach ($agresores as $a) {
            if ($a['id'] === $_POST['editar']) {
                $agresor = $a;
                $editando = true;
            }
        }
    }

    // REGISTRAR O MODIFICAR UN AGRESOR

    if (isset($_POST['guardar']) || isset($_POST['modificar'])) {

        $id = trim($_POST['id'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $antecedentes = trim($_POST['antecedentes'] ?? '');
        $observaciones = trim($_POST['observaciones'] ?? '');

        // Comprobar los indicadores

        $respuestas = [];

        foreach ($indicadores as $clave => $texto) {
            $respuesta = $_POST['indicadores'][$clave] ?? 'Desconocido';

            if (!in_array($respuesta, ['Sí', 'No', 'Desconocido'], true)) {
                $respuesta = 'Desconocido';
            }

            $respuestas[$clave] = $respuesta;
        }

        // Si hay un error, el formulario conserva lo que se escribió

        $agresor = [
            'id' => $id,
            'nombre' => $nombre,
            'antecedentes' => $antecedentes,
            'indicadores' => $respuestas,
            'observaciones' => $observaciones
        ];

        $editando = isset($_POST['modificar']);

        // Buscar si ya existe un agresor con ese identificador

        $posicion = -1;

        foreach ($agresores as $i => $a) {
            if ($a['id'] === $id) {
                $posicion = $i;
            }
        }

        // Validar y guardar

        if ($id === '' || $nombre === '') {
            $mensaje = 'El identificador y el nombre son obligatorios.';

        } elseif (isset($_POST['guardar']) && $posicion !== -1) {
            $mensaje = 'Ya existe un agresor con ese identificador.';

        } elseif (isset($_POST['modificar']) && $posicion === -1) {
            $mensaje = 'No existe un agresor con ese identificador.';

        } else {

            if ($posicion === -1) {
                $agresores[] = $agresor;
                $mensaje = 'Agresor registrado correctamente.';
            } else {
                $agresores[$posicion] = $agresor;
                $mensaje = 'Agresor modificado correctamente.';
            }

            // Guardar en JSON

            $resultado = file_put_contents(
                $archivo,
                json_encode(
                    $agresores,
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
                ),
                LOCK_EX
            );

            if ($resultado === false) {
                $mensaje = 'Error al guardar el agresor.';
            } else {
                // Vaciar el formulario
                $agresor = [
                    'id' => '',
                    'nombre' => '',
                    'antecedentes' => '',
                    'indicadores' => [],
                    'observaciones' => ''
                ];
                $editando = false;
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
    <title>Gestión de presuntos agresores</title>
</head>

<body>

    <h1>Gestión de presuntos agresores</h1>

    <p>
        Registra únicamente datos ficticios. Los indicadores son orientativos
        y no sustituyen una valoración profesional.
    </p>

    <?php if ($mensaje !== ''): ?>
        <p class="mensaje"><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>

    <h2><?= $editando ? 'Modificar agresor' : 'Registrar agresor' ?></h2>

    <form method="POST">

        <label>Identificador único</label>
        <input type="text" name="id" value="<?= htmlspecialchars($agresor['id']) ?>" <?= $editando ? 'readonly' : '' ?>>

        <label>Nombre y apellidos</label>
        <input type="text" name="nombre" value="<?= htmlspecialchars($agresor['nombre']) ?>">

        <label>Antecedentes relevantes documentados</label>
        <textarea name="antecedentes"><?= htmlspecialchars($agresor['antecedentes']) ?></textarea>

        <h3>Indicadores relacionados con el caso</h3>

        <?php foreach ($indicadores as $clave => $texto): ?>
            <fieldset>
                <legend><?= htmlspecialchars($texto) ?></legend>

                <?php foreach (['Sí', 'No', 'Desconocido'] as $opcion): ?>
                    <label>
                        <input type="radio" name="indicadores[<?= $clave ?>]" value="<?= $opcion ?>"
                            <?= ($agresor['indicadores'][$clave] ?? 'Desconocido') === $opcion ? 'checked' : '' ?>>
                        <?= $opcion ?>
                    </label>
                <?php endforeach; ?>

            </fieldset>
        <?php endforeach; ?>

        <label>Observaciones</label>
        <textarea name="observaciones"><?= htmlspecialchars($agresor['observaciones']) ?></textarea>

        <?php if ($editando): ?>
            <button name="modificar">Guardar modificación</button>
        <?php else: ?>
            <button name="guardar">Registrar agresor</button>
        <?php endif; ?>

    </form>

    <h2>Agresores registrados</h2>

    <?php if (count($agresores) === 0): ?>
        <p>No hay agresores registrados todavía.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Identificador</th>
                    <th>Nombre</th>
                    <th>Antecedentes</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($agresores as $a): ?>
                    <tr>
                        <td><?= htmlspecialchars($a['id']) ?></td>
                        <td><?= htmlspecialchars($a['nombre']) ?></td>
                        <td><?= htmlspecialchars($a['antecedentes']) ?></td>
                        <td>
                            <form method="POST">
                                <button name="editar" value="<?= htmlspecialchars($a['id']) ?>">Modificar</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

    <h2>Navegación</h2>

    <form method="POST">
        <button name="inicio">Inicio</button>
        <button name="incidencias">Incidencias</button>
        <button name="victimas">Víctimas</button>
        <button name="agresores">Agresores</button>
        <button name="valoracion">Valoración</button>
        <button name="informe">Informe</button>
    </form>

</body>
</html>