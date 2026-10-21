<?php
// Funciones para registrar, modificar y cargar agresores.

function gestionar_agresores(&$agresores, &$datos, &$mensaje)
{
    // Cargar un agresor en el formulario para modificarlo

    if (isset($_POST['editar'])) {
        $agresor = buscar_registro($agresores, post_texto('editar'));

        if ($agresor === null) {
            $mensaje = 'No se encontró el agresor seleccionado.';
        } else {
            $datos['agresor'] = array_merge(datos_vacios()['agresor'], $agresor);
            $datos['agresor']['indicadores'] = respuestas_validas(INDICADORES_AGRESOR, $agresor['indicadores'] ?? []);
        }
    }

    // Registrar o modificar

    if (isset($_POST['registrar']) || isset($_POST['modificar'])) {

        $agresor = $datos['agresor'];
        $posicion = posicion_registro($agresores, $agresor['id']);

        if ($agresor['id'] === '' || $agresor['nombre'] === '') {
            $mensaje = 'El identificador y el nombre son obligatorios.';

        } elseif (isset($_POST['registrar']) && $posicion !== -1) {
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

            if (guardar_json(ARCHIVO_AGRESORES, $agresores)) {
                $datos['agresor'] = datos_vacios()['agresor'];
            } else {
                $agresores = leer_json(ARCHIVO_AGRESORES);
                $mensaje = 'Error al guardar el agresor.';
            }
        }
    }
}
