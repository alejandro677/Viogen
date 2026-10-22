<?php
// Funciones para registrar, modificar y cargar víctimas.

function gestionar_formulario_victimas(&$lista_victimas, &$datos_formularios, &$mensaje_usuario)
{
    // Cargar una víctima en el formulario para modificarla

    if (isset($_POST['editar'])) {
        $victima_encontrada = buscar_registro_por_id($lista_victimas, obtener_texto_enviado('editar'));

        if ($victima_encontrada === null) {
            $mensaje_usuario = 'No se encontró la víctima seleccionada.';
        } else {
            $datos_formularios['victima'] = array_merge(crear_datos_formularios_vacios()['victima'], $victima_encontrada);
            $datos_formularios['victima']['indicadores'] = filtrar_respuestas_validas(INDICADORES_VICTIMA, $victima_encontrada['indicadores'] ?? []);
        }
    }

    // Registrar o modificar

    if (isset($_POST['registrar']) || isset($_POST['modificar'])) {

        $datos_victima = $datos_formularios['victima'];
        $posicion_victima = buscar_posicion_por_id($lista_victimas, $datos_victima['id']);

        if ($datos_victima['id'] === '' || $datos_victima['nombre'] === '') {
            $mensaje_usuario = 'El identificador y el nombre son obligatorios.';

        } elseif ($datos_victima['edad'] !== '' && !ctype_digit($datos_victima['edad'])) {
            $mensaje_usuario = 'La edad debe ser un número.';

        } elseif (isset($_POST['registrar']) && $posicion_victima !== -1) {
            $mensaje_usuario = 'Ya existe una víctima con ese identificador.';

        } elseif (isset($_POST['modificar']) && $posicion_victima === -1) {
            $mensaje_usuario = 'No existe una víctima con ese identificador.';

        } else {

            if ($posicion_victima === -1) {
                $lista_victimas[] = $datos_victima;
                $mensaje_usuario = 'Víctima registrada correctamente.';
            } else {
                $lista_victimas[$posicion_victima] = $datos_victima;
                $mensaje_usuario = 'Víctima modificada correctamente.';
            }

            // Guardar en JSON

            if (guardar_archivo_json(RUTA_ARCHIVO_VICTIMAS, $lista_victimas)) {
                $datos_formularios['victima'] = crear_datos_formularios_vacios()['victima'];
            } else {
                $lista_victimas = leer_archivo_json(RUTA_ARCHIVO_VICTIMAS);
                $mensaje_usuario = 'Error al guardar la víctima.';
            }
        }
    }
}
