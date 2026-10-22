<?php
// Funciones para registrar, modificar y cargar agresores.

function gestionar_formulario_agresores(&$lista_agresores, &$datos_formularios, &$mensaje_usuario)
{
    // Cargar un agresor en el formulario para modificarlo

    if (isset($_POST['editar'])) {
        $agresor_encontrado = buscar_registro_por_id($lista_agresores, obtener_texto_enviado('editar'));

        if ($agresor_encontrado === null) {
            $mensaje_usuario = 'No se encontró el agresor seleccionado.';
        } else {
            $datos_formularios['agresor'] = array_merge(crear_datos_formularios_vacios()['agresor'], $agresor_encontrado);
            $datos_formularios['agresor']['indicadores'] = filtrar_respuestas_validas(INDICADORES_AGRESOR, $agresor_encontrado['indicadores'] ?? []);
        }
    }

    // Registrar o modificar

    if (isset($_POST['registrar']) || isset($_POST['modificar'])) {

        $datos_agresor = $datos_formularios['agresor'];
        $posicion_agresor = buscar_posicion_por_id($lista_agresores, $datos_agresor['id']);

        if ($datos_agresor['id'] === '' || $datos_agresor['nombre'] === '') {
            $mensaje_usuario = 'El identificador y el nombre son obligatorios.';

        } elseif (isset($_POST['registrar']) && $posicion_agresor !== -1) {
            $mensaje_usuario = 'Ya existe un agresor con ese identificador.';

        } elseif (isset($_POST['modificar']) && $posicion_agresor === -1) {
            $mensaje_usuario = 'No existe un agresor con ese identificador.';

        } else {

            if ($posicion_agresor === -1) {
                $lista_agresores[] = $datos_agresor;
                $mensaje_usuario = 'Agresor registrado correctamente.';
            } else {
                $lista_agresores[$posicion_agresor] = $datos_agresor;
                $mensaje_usuario = 'Agresor modificado correctamente.';
            }

            // Guardar en JSON

            if (guardar_archivo_json(RUTA_ARCHIVO_AGRESORES, $lista_agresores)) {
                $datos_formularios['agresor'] = crear_datos_formularios_vacios()['agresor'];
            } else {
                $lista_agresores = leer_archivo_json(RUTA_ARCHIVO_AGRESORES);
                $mensaje_usuario = 'Error al guardar el agresor.';
            }
        }
    }
}
