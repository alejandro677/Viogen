<?php
// NAVEGACION
if ($_SERVER['REQUEST_METHOD'] === 'POST'){

    if (isset($_POST['inicio'])){
        require('index.php');
        die();
    }

    if (isset($_POST['incidencias'])){
        require('incidencias.php');
        die();
    }

    if (isset($_POST['victimas'])){
        require('victimas.php');
        die();
    }

    if (isset($_POST['agresores'])){
        require('agresores.php');
        die();
    }

    if (isset($_POST['valoracion'])){
        require('valoracion.php');
        die();
    }

    if (isset($_POST['informe'])){
        require('informe.php');
        die();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <link rel="stylesheet" href="css/estilos.css">
    <title>Proyecto VioGén</title>
</head>

<body>

    <h1>Proyecto VioGén</h1>

    <h2>Presentación del proyecto</h2>

    <p>
        Esta aplicación web tiene como objetivo gestionar información
        relacionada con incidencias de violencia de género mediante
        formularios desarrollados en PHP.
    </p>

    <p>
        Permite registrar y consultar víctimas, presuntos agresores
        e incidencias, recoger indicadores orientativos de riesgo
        y generar un informe final con la información del caso.
    </p>

    <p>
        Este proyecto tiene una finalidad exclusivamente académica.
        No reproduce el sistema oficial VioGén ni sustituye una
        valoración profesional. Se utilizarán únicamente datos ficticios.
    </p>

    <h2>Participantes del proyecto</h2>

    <ul>
        <li>Integrante 1: Alberto Jimenez Hernandez</li>
        <li>Integrante 2: Izan Cano Flores</li>
        <li>Integrante 3: Alejandro Garrido Griñon</li>
    </ul>

    <h2>Menú de navegación</h2>

    <p>Selecciona la sección a la que quieras acceder.</p>

    <form method="POST">

        <button name="inicio">Inicio</button>
        <br><br>

        <button name="incidencias">Gestión de incidencias</button>
        <br><br>

        <button name="victimas">Gestión de víctimas</button>
        <br><br>

        <button name="agresores">Gestión de presuntos agresores</button>
        <br><br>

        <button name="valoracion">Valoración orientativa del riesgo</button>
        <br><br>

        <button name="informe">Informe final</button>

    </form>

</body>
</html>
