<?php

$victimas = [];

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
}

// DATOS TEMPORALES

if (isset($_POST['datos'])) {
    $victimas = json_decode($_POST['datos'], true);
}

if (isset($_POST['registrar'])) {
    $id = $_POST['id'];

    $victimas[$id] = [
        'nombre' => htmlspecialchars($_POST['nombre']),
        'edad' => $_POST['edad'],
        'observaciones' => htmlspecialchars($_POST['observaciones'])
    ];
}

if (isset($_POST['modificar'])) {
    $id = $_POST['id'];

    if (isset($victimas[$id])) {
        $victimas[$id]['nombre'] = htmlspecialchars($_POST['nombre']);
        $victimas[$id]['edad'] = $_POST['edad'];
        $victimas[$id]['observaciones'] = htmlspecialchars($_POST['observaciones']);
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

    <form method="POST">

        <input type="hidden" name="datos" value="<?= htmlspecialchars(json_encode($victimas)) ?>">

        <label>Identificador único</label>
        <input type="text" name="id">

        <label>Nombre y apellidos ficticios</label>
        <input type="text" name="nombre">

        <label>Edad</label>
        <input type="number" name="edad">

        <label>Observaciones</label>
        <textarea name="observaciones"></textarea>

        <button name="registrar">Registrar</button>
        <button name="modificar">Modificar</button>

        <h2>Navegación</h2>

        <button name="inicio">Inicio</button>
        <button name="incidencias">Incidencias</button>
        <button name="agresores">Agresores</button>
        <button name="valoracion">Valoración</button>
        <button name="informe">Informe</button>

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
                </tr>
            </thead>
            <tbody>
                <?php foreach ($victimas as $id => $victima): ?>
                    <tr>
                        <td><?= htmlspecialchars($id) ?></td>
                        <td><?= $victima['nombre'] ?></td>
                        <td><?= htmlspecialchars($victima['edad']) ?></td>
                        <td><?= $victima['observaciones'] ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>

</body>
</html>