<?php
// FUNCIONES Y CONSTANTES COMUNES DEL PROYECTO

// RUTAS DE LOS ARCHIVOS JSON (datos persistentes)

const RUTA_ARCHIVO_VICTIMAS = 'datos/victimas.json';
const RUTA_ARCHIVO_AGRESORES = 'datos/agresores.json';
const RUTA_ARCHIVO_INCIDENCIAS = 'datos/incidencias.json';
const RUTA_ARCHIVO_VALORACIONES = 'datos/valoraciones.json';

// RESPUESTAS QUE SE PUEDEN ELEGIR EN CADA INDICADOR

const RESPUESTAS_PERMITIDAS = ['Sí', 'No', 'Desconocido'];

// INDICADORES DE RIESGO DE LA INCIDENCIA Y DE LA VALORACION

const INDICADORES_INCIDENCIA = [
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

// INDICADORES DE LA VÍCTIMA (numeración interna del 1 al 9)

const INDICADORES_VICTIMA = [
    'convivencia_alejamiento' => 'Indicador 1. Ha reanudado la convivencia con el agresor estando en vigor una medida de alejamiento',
    'retirada_denuncia' => 'Indicador 2. No declara sobre episodios denunciables o manifiesta que desea retirar la denuncia o rechazar la protección',
    'conductas_inseguras' => 'Indicador 3. Realiza actividades que van en contra de su propia seguridad',
    'discapacidad_enfermedad' => 'Indicador 4. Presenta discapacidad o enfermedad física o psíquica grave',
    'falta_apoyo' => 'Indicador 5. Carece de apoyo familiar o social favorable',
    'ideas_suicidio' => 'Indicador 6. Muestra ideas o intentos de suicidio',
    'adicciones' => 'Indicador 7. Presenta adicción o abuso de alcohol, drogas o fármacos',
    'menores_cargo' => 'Indicador 8. Tiene menores de edad a su cargo',
    'conflicto_cuidado_hijos' => 'Indicador 9. Existe conflicto por las responsabilidades de cuidado de los hijos'
];

// INDICADORES DEL AGRESOR (numeración interna del 1 al 18)

const INDICADORES_AGRESOR = [
    'celos' => 'Indicador 1. En los últimos seis meses muestra celos exagerados o sospechas de infidelidad',
    'control' => 'Indicador 2. En los últimos seis meses muestra conductas de control',
    'acoso' => 'Indicador 3. En los últimos seis meses muestra conductas de acoso',
    'fugado' => 'Indicador 4. Está fugado o en paradero desconocido',
    'antecedentes' => 'Indicador 5. Tiene antecedentes penales o policiales',
    'quebrantamiento' => 'Indicador 6. Existen quebrantamientos de medidas cautelares o penales',
    'trastorno_mental' => 'Indicador 7. Presenta un trastorno mental o psiquiátrico',
    'ideas_suicidio' => 'Indicador 8. Muestra ideas o intentos de suicidio',
    'adicciones' => 'Indicador 9. Presenta adicción o abuso de alcohol, drogas o fármacos',
    'separacion_no_deseada' => 'Indicador 10. Existen trámites de separación o divorcio no deseados por el agresor',
    'distanciamiento' => 'Indicador 11. Se ha distanciado de la víctima',
    'respeto_victima' => 'Indicador 12. Muestra respeto a la víctima y no expresa ánimo de venganza contra ella o su entorno',
    'colaboracion_ley' => 'Indicador 13. Muestra respeto hacia la ley y colabora con los agentes',
    'arrepentimiento' => 'Indicador 14. Muestra arrepentimiento',
    'programas_ayuda' => 'Indicador 15. Se acoge a programas de ayuda',
    'cumple_responsabilidades' => 'Indicador 16. Cumple el régimen de separación y las cargas familiares',
    'estabilidad_economica' => 'Indicador 17. Tiene una situación laboral y económica estable',
    'apoyo_social' => 'Indicador 18. Cuenta con apoyo social y familiar favorable'
];

// ESCAPAR UN TEXTO PARA MOSTRARLO EN HTML

function escapar_html($texto_a_escapar)
{
    return htmlspecialchars((string) $texto_a_escapar, ENT_QUOTES, 'UTF-8');
}

// LEER UN ARCHIVO JSON (si no existe, lo crea con un array vacío)

function leer_archivo_json($ruta_archivo)
{
    $carpeta_datos = dirname($ruta_archivo);

    if (!is_dir($carpeta_datos)) {
        mkdir($carpeta_datos);
    }

    if (!file_exists($ruta_archivo)) {
        file_put_contents($ruta_archivo, '[]');
    }

    $registros = json_decode((string) file_get_contents($ruta_archivo), true);

    return is_array($registros) ? $registros : [];
}

// GUARDAR UN ARRAY EN UN ARCHIVO JSON (devuelve true si se guardó)

function guardar_archivo_json($ruta_archivo, $registros)
{
    $texto_json = json_encode($registros, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    if ($texto_json === false) {
        return false;
    }

    return file_put_contents($ruta_archivo, $texto_json, LOCK_EX) !== false;
}

// DATOS TEMPORALES: lo que hay escrito en cada formulario

function crear_datos_formularios_vacios()
{
    return [
        'victima' => ['id' => '', 'nombre' => '', 'edad' => '', 'indicadores' => [], 'observaciones' => ''],
        'agresor' => ['id' => '', 'nombre' => '', 'antecedentes' => '', 'indicadores' => [], 'observaciones' => ''],
        'incidencia' => ['fecha' => '', 'lugar' => '', 'descripcion' => '', 'id_victima' => '', 'id_agresor' => '', 'indicadores' => [], 'observaciones' => ''],
        'valoracion' => ['id_incidencia' => '', 'indicadores' => [], 'observaciones' => ''],
        'informe' => ['id_incidencia' => '']
    ];
}

// Recuperar los datos temporales que llegan en el campo oculto (JSON)

function recuperar_datos_formularios($texto_json)
{
    $datos_formularios = crear_datos_formularios_vacios();
    $datos_recibidos = json_decode($texto_json, true);

    if (!is_array($datos_recibidos)) {
        return $datos_formularios;
    }

    foreach ($datos_formularios as $nombre_seccion => $campos_vacios) {
        if (isset($datos_recibidos[$nombre_seccion]) && is_array($datos_recibidos[$nombre_seccion])) {
            $datos_formularios[$nombre_seccion] = array_merge($campos_vacios, $datos_recibidos[$nombre_seccion]);
        }
    }

    return $datos_formularios;
}

// LEER UN CAMPO DE TEXTO ENVIADO POR EL FORMULARIO (siempre devuelve un texto)

function obtener_texto_enviado($nombre_campo)
{
    $valor_enviado = $_POST[$nombre_campo] ?? '';

    return is_string($valor_enviado) ? trim($valor_enviado) : '';
}

// COMPROBAR LAS RESPUESTAS DE LOS INDICADORES (Sí / No / Desconocido)

function filtrar_respuestas_validas($indicadores_permitidos, $respuestas_enviadas)
{
    $respuestas_validas = [];

    foreach ($indicadores_permitidos as $clave_indicador => $texto_indicador) {
        $respuesta = is_array($respuestas_enviadas) ? ($respuestas_enviadas[$clave_indicador] ?? 'Desconocido') : 'Desconocido';

        if (!in_array($respuesta, RESPUESTAS_PERMITIDAS, true)) {
            $respuesta = 'Desconocido';
        }

        $respuestas_validas[$clave_indicador] = $respuesta;
    }

    return $respuestas_validas;
}

// BUSCAR UN REGISTRO POR SU IDENTIFICADOR (devuelve null si no existe)

function buscar_registro_por_id($lista_registros, $identificador)
{
    foreach ($lista_registros as $registro) {
        if (($registro['id'] ?? '') === $identificador) {
            return $registro;
        }
    }

    return null;
}

// POSICION DE UN REGISTRO EN LA LISTA (devuelve -1 si no existe)

function buscar_posicion_por_id($lista_registros, $identificador)
{
    foreach ($lista_registros as $posicion => $registro) {
        if (($registro['id'] ?? '') === $identificador) {
            return $posicion;
        }
    }

    return -1;
}

// SEPARAR LOS INDICADORES DETECTADOS ("Sí") Y LOS PENDIENTES ("Desconocido")

function separar_indicadores_detectados_y_pendientes($indicadores_permitidos, $respuestas_indicadores)
{
    $indicadores_detectados = [];
    $indicadores_pendientes = [];

    foreach ($indicadores_permitidos as $clave_indicador => $texto_indicador) {
        $respuesta = $respuestas_indicadores[$clave_indicador] ?? 'Desconocido';

        if ($respuesta === 'Sí') {
            $indicadores_detectados[] = $texto_indicador;
        }

        if ($respuesta === 'Desconocido') {
            $indicadores_pendientes[] = $texto_indicador;
        }
    }

    return [$indicadores_detectados, $indicadores_pendientes];
}

// NIVEL ACADEMICO ORIENTATIVO (regla didáctica propia, no es la oficial)

function calcular_nivel_riesgo_orientativo($total_indicadores_detectados)
{
    if ($total_indicadores_detectados <= 2) {
        return 'Bajo';
    }

    if ($total_indicadores_detectados <= 5) {
        return 'Medio';
    }

    if ($total_indicadores_detectados <= 8) {
        return 'Alto';
    }

    return 'Muy alto';
}
