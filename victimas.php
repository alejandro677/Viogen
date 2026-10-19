<?php

$victimas = [];

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

require 'victimas.html';

?>