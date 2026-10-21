<?php
// FUNCIONES COMUNES DEL PROYECTO

// ARCHIVOS JSON (datos persistentes)

const ARCHIVO_VICTIMAS = 'datos/victimas.json';
const ARCHIVO_AGRESORES = 'datos/agresores.json';
const ARCHIVO_INCIDENCIAS = 'datos/incidencias.json';
const ARCHIVO_VALORACIONES = 'datos/valoraciones.json';

// RESPUESTAS PERMITIDAS EN LOS INDICADORES

const RESPUESTAS = ['Sí', 'No', 'Desconocido'];

// INDICADORES DE RIESGO DE LA INCIDENCIA Y DE LA VALORACION

const INDICADORES = [
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
    'conflicto_cuidado_hijos' => 'Indicador 1. Existe conflicto por las responsabilidades de cuidado de los hijos'
];

// INDICADORES DEL AGRESOR (numeración interna del 1 al 18)

const INDICADORES_AGRESOR = [
    'celos' => 'Indicador 9. En los últimos seis meses muestra celos exagerados o sospechas de infidelidad',
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

function e($valor)
{
    return htmlspecialchars((string) $valor, ENT_QUOTES, 'UTF-8');
}

// LEER UN ARCHIVO JSON (si no existe, lo crea con un array vacío)

function leer_json($archivo)
{
    $carpeta = dirname($archivo);

    if (!is_dir($carpeta)) {
        mkdir($carpeta);
    }

    if (!file_exists($archivo)) {
        file_put_contents($archivo, '[]');
    }

    $datos = json_decode((string) file_get_contents($archivo), true);

    return is_array($datos) ? $datos : [];
}

// GUARDAR UN ARRAY EN UN ARCHIVO JSON (devuelve true si se guardó)

function guardar_json($archivo, $datos)
{
    $texto = json_encode($datos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    if ($texto === false) {
        return false;
    }

    return file_put_contents($archivo, $texto, LOCK_EX) !== false;
}

// DATOS TEMPORALES: lo que hay escrito en cada formulario

function datos_vacios()
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

function recuperar_datos($texto)
{
    $datos = datos_vacios();
    $recibidos = json_decode($texto, true);

    if (!is_array($recibidos)) {
        return $datos;
    }

    foreach ($datos as $seccion => $campos) {
        if (isset($recibidos[$seccion]) && is_array($recibidos[$seccion])) {
            $datos[$seccion] = array_merge($campos, $recibidos[$seccion]);
        }
    }

    return $datos;
}

// LEER UN CAMPO DE TEXTO DEL FORMULARIO (siempre devuelve un texto)

function post_texto($campo)
{
    $valor = $_POST[$campo] ?? '';

    return is_string($valor) ? trim($valor) : '';
}

// COMPROBAR LAS RESPUESTAS DE LOS INDICADORES (Sí / No / Desconocido)

function respuestas_validas($indicadores, $enviadas)
{
    $respuestas = [];

    foreach ($indicadores as $clave => $nombre) {
        $respuesta = is_array($enviadas) ? ($enviadas[$clave] ?? 'Desconocido') : 'Desconocido';

        if (!in_array($respuesta, RESPUESTAS, true)) {
            $respuesta = 'Desconocido';
        }

        $respuestas[$clave] = $respuesta;
    }

    return $respuestas;
}

// BUSCAR UN REGISTRO POR SU IDENTIFICADOR (devuelve null si no existe)

function buscar_registro($lista, $id)
{
    foreach ($lista as $registro) {
        if (($registro['id'] ?? '') === $id) {
            return $registro;
        }
    }

    return null;
}

// POSICION DE UN REGISTRO EN LA LISTA (devuelve -1 si no existe)

function posicion_registro($lista, $id)
{
    foreach ($lista as $posicion => $registro) {
        if (($registro['id'] ?? '') === $id) {
            return $posicion;
        }
    }

    return -1;
}

// SEPARAR LOS FACTORES DETECTADOS ("Sí") Y LOS PENDIENTES ("Desconocido")

function clasificar_respuestas($indicadores, $respuestas)
{
    $detectados = [];
    $pendientes = [];

    foreach ($indicadores as $clave => $nombre) {
        $respuesta = $respuestas[$clave] ?? 'Desconocido';

        if ($respuesta === 'Sí') {
            $detectados[] = $nombre;
        }

        if ($respuesta === 'Desconocido') {
            $pendientes[] = $nombre;
        }
    }

    return [$detectados, $pendientes];
}

// NIVEL ACADEMICO ORIENTATIVO (regla didáctica propia, no es la oficial)

function nivel_riesgo($total)
{
    if ($total <= 2) {
        return 'Bajo';
    }

    if ($total <= 5) {
        return 'Medio';
    }

    if ($total <= 8) {
        return 'Alto';
    }

    return 'Muy alto';
}
