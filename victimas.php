<?php
// Funciones para registrar, modificar y cargar victimas.

function gestionar_victimas(&$victimas, &$datos, &$mensaje)
{
    // Cargar una víctima en el formulario para modificarla

    if (isset($_POST['editar'])) {
        $victima = buscar_registro($victimas, post_texto('editar'));

        if ($victima === null) {
            $mensaje = 'No se encontró la víctima seleccionada.';
        } else {
            $datos['victima'] = array_merge(datos_vacios()['victima'], $victima);
            $datos['victima']['indicadores'] = respuestas_validas(INDICADORES_VICTIMA, $victima['indicadores'] ?? []);
        }
    }

    // Registrar o modificar

    if (isset($_POST['registrar']) || isset($_POST['modificar'])) {

        $victima = $datos['victima'];
        $posicion = posicion_registro($victimas, $victima['id']);

        if ($victima['id'] === '' || $victima['nombre'] === '') {
            $mensaje = 'El identificador y el nombre son obligatorios.';

        } elseif ($victima['edad'] !== '' && !ctype_digit($victima['edad'])) {
            $mensaje = 'La edad debe ser un número.';

        } elseif (isset($_POST['registrar']) && $posicion !== -1) {
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

            if (guardar_json(ARCHIVO_VICTIMAS, $victimas)) {
                $datos['victima'] = datos_vacios()['victima'];
            } else {
                $victimas = leer_json(ARCHIVO_VICTIMAS);
                $mensaje = 'Error al guardar la víctima.';
            }
        }
    }
}
