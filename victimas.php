<?php
// LEER LOS DATOS JSON

// Crear la carpeta datos si no existe

if (!is_dir('datos')) {
    mkdir('datos');
}

$archivo = 'datos/victimas.json';

// Comprobar que existe el archivo

if (!file_exists($archivo)) {
    file_put_contents($archivo, '[]');
}

// Obtener los registros

$victimas = json_decode(file_get_contents($archivo), true);

if (!is_array($victimas)) $victimas = [];

$mensaje = '';

// DATOS DEL FORMULARIO (vacíos por defecto)

$victima = [
    'id' => '',
    'nombre' => '',
    'edad' => '',
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

    // CARGAR UNA VICTIMA EN EL FORMULARIO PARA MODIFICARLA

    if (isset($_POST['editar'])) {

        foreach ($victimas as $v) {
            if ($v['id'] === $_POST['editar']) {
                $victima = $v;
                $editando = true;
            }
        }
    }

    // REGISTRAR O MODIFICAR UNA VICTIMA

    if (isset($_POST['guardar']) || isset($_POST['modificar'])) {

        $id = trim($_POST['id'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $edad = trim($_POST['edad'] ?? '');
        $observaciones = trim($_POST['observaciones'] ?? '');

        // Si hay un error, el formulario conserva lo que se escribió

        $victima = [
            'id' => $id,
            'nombre' => $nombre,
            'edad' => $edad,
            'observaciones' => $observaciones
        ];

        $editando = isset($_POST['modificar']);

        // Buscar si ya existe una víctima con ese identificador

        $posicion = -1;

        foreach ($victimas as $i => $v) {
            if ($v['id'] === $id) {
                $posicion = $i;
            }
        }

        // Validar y guardar

        if ($id === '' || $nombre === '') {
            $mensaje = 'El identificador y el nombre son obligatorios.';

        } elseif ($edad !== '' && !ctype_digit($edad)) {
            $mensaje = 'La edad debe ser un número.';

        } elseif (isset($_POST['guardar']) && $posicion !== -1) {
            $mensaje = 'Ya existe una víctima con ese identificador.';

        } elseif (isset($_POST['modificar']) && $posicion === -1) {
            $mensaje = 'No existe una víctima con ese identificador.';

        } else {

            if ($posicion === -1) {
                $victimas[] = $victima;
                $mensaje = 'Víctima registrada correctamente.';
            } else {
                $victimas[$posicion] = $victima;
                $mensaje = 'Víctima modificada correctamente.';
            }

            // Guardar en JSON

            $resultado = file_put_contents(
                $archivo,
                json_encode(
                    $victimas,
                    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
                ),
                LOCK_EX
            );

            if ($resultado === false) {
                $mensaje = 'Error al guardar la víctima.';
            } else {
                // Vaciar el formulario
                $victima = [
                    'id' => '',
                    'nombre' => '',
                    'edad' => '',
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
    <title>Gestión de víctimas</title>
</head>

<body>

    <h1>Gestión de víctimas</h1>

    <p>Registra únicamente datos ficticios.</p>

    <?php if ($mensaje !== ''): ?>
        <p class="mensaje"><?= htmlspecialchars($mensaje) ?></p>
    <?php endif; ?>

    <h2><?= $editando ? 'Modificar víctima' : 'Registrar víctima' ?></h2>

    <form method="POST">

        <label>Identificador único</label>
        <input type="text" name="id" value="<?= htmlspecialchars($victima['id']) ?>" <?= $editando ? 'readonly' : '' ?>>

        <label>Nombre y apellidos ficticios</label>
        <input type="text" name="nombre" value="<?= htmlspecialchars($victima['nombre']) ?>">

        <label>Edad</label>
        <input type="number" name="edad" min="0" value="<?= htmlspecialchars($victima['edad']) ?>">

        <label>Observaciones</label>
        <textarea name="observaciones"><?= htmlspecialchars($victima['observaciones']) ?></textarea>

        <?php if ($editando): ?>
            <button name="modificar">Guardar modificación</button>
        <?php else: ?>
            <button name="guardar">Registrar víctima</button>
        <?php endif; ?>

    </form>

    <h2>Víctimas registradas</h2>

    <?php if (count($victimas) === 0): ?>
        <p>No hay víctimas registradas todavía.</p>
    <?php else: ?>
        <table>
            <thead>
                <tr>
                    <th>Identificador</th>
                    <th>Nombre</th>
                    <th>Edad</th>
                    <th>Observaciones</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($victimas as $v): ?>
                    <tr>
                        <td><?= htmlspecialchars($v['id']) ?></td>
                        <td><?= htmlspecialchars($v['nombre']) ?></td>
                        <td><?= htmlspecialchars($v['edad']) ?></td>
                        <td><?= htmlspecialchars($v['observaciones']) ?></td>
                        <td>
                            <form method="POST">
                                <button name="editar" value="<?= htmlspecialchars($v['id']) ?>">Modificar</button>
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
        <button name="agresores">Agresores</button>
        <button name="valoracion">Valoración</button>
        <button name="informe">Informe</button>
    </form>

</body>
</html>