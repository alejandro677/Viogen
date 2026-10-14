# Proyecto VioGén

## Descripción

Aplicación web académica desarrollada en PHP para gestionar incidencias relacionadas con la violencia de género. Permite registrar y consultar víctimas, presuntos agresores e incidencias, realizar una valoración orientativa del riesgo mediante indicadores y generar un informe final con la información recopilada.

Los datos se almacenan en archivos JSON y se gestionan mediante formularios PHP.

> **Aviso:** este proyecto tiene una finalidad exclusivamente académica. No reproduce el algoritmo oficial de VioGén ni sustituye una valoración profesional o policial. Para las pruebas deben utilizarse únicamente datos ficticios.

## Objetivos

- Crear una aplicación web sencilla con PHP.
- Registrar, consultar y modificar víctimas, agresores e incidencias.
- Relacionar los registros mediante identificadores únicos.
- Conservar los datos durante la navegación entre formularios mediante `POST` y JSON.
- Guardar la información de forma persistente en archivos JSON.
- Recoger indicadores de riesgo con respuestas «Sí», «No» o «Desconocido».
- Generar un informe que resuma la incidencia y justifique el resultado académico orientativo.
- Permitir la navegación libre entre las secciones.

## Funcionalidades

### 1. Página de inicio — `index.php`

- Presentación del proyecto y de sus participantes.
- Menú de navegación a todas las secciones.
- Acceso directo a cada formulario, sin obligar a seguir un orden lineal.

### 2. Gestión de incidencias — `incidencias.php`

Permitirá registrar y consultar incidencias con información como:

- Identificador de la incidencia.
- Fecha y lugar de los hechos.
- Descripción de lo ocurrido.
- Identificador de la víctima relacionada.
- Identificador del presunto agresor relacionado.
- Indicadores de riesgo y observaciones asociadas.

### 3. Gestión de víctimas — `victimas.php`

Permitirá registrar, consultar y modificar víctimas de prueba. Los campos previstos son:

- Identificador único.
- Nombre y apellidos ficticios.
- Edad o fecha de nacimiento, si es necesaria.
- Datos de contacto, únicamente si el ejercicio lo requiere.
- Observaciones relevantes para el caso.

### 4. Gestión de presuntos agresores — `agresores.php`

Permitirá registrar, consultar y modificar datos de prueba, como:

- Identificador único.
- Nombre y apellidos ficticios.
- Antecedentes relevantes documentados, cuando proceda.
- Información relacionada con los indicadores del caso.
- Observaciones.

### 5. Valoración orientativa — `valoracion.php`

- Selección de una incidencia registrada.
- Consulta de la información relacionada disponible.
- Formulario con al menos diez indicadores.
- Respuestas «Sí», «No» o «Desconocido».
- Identificación de factores detectados e información pendiente.
- Presentación y almacenamiento del resultado académico orientativo.

Los indicadores propuestos para el ejercicio son variables orientativas y deben contrastarse con la documentación oficial elegida. No son una reproducción oficial de los indicadores, ponderaciones ni del algoritmo de VioGén.

### 6. Informe final — `informe.php`

El informe reunirá la información de una incidencia concreta e incluirá:

1. **Identificación del caso:** código, fecha y referencias de víctima y agresor.
2. **Resumen de los hechos:** descripción de la incidencia.
3. **Indicadores de riesgo:** respuestas y observaciones.
4. **Factores detectados y pendientes:** información relevante y datos desconocidos.
5. **Valoración final:** resultado académico orientativo y explicación de cómo se obtiene.
6. **Observaciones:** fecha de elaboración y advertencia sobre las limitaciones del modelo.

El informe debe generarse a partir de los datos guardados, para que pueda consultarse de nuevo y esté vinculado a la incidencia correspondiente.

## Estructura del proyecto

```text
viogen/
├── index.php
├── incidencias.php
├── victimas.php
├── agresores.php
├── valoracion.php
├── informe.php
├── funciones.php             # Propuesta para centralizar operaciones JSON
├── datos/
│   ├── incidencias.json
│   ├── victimas.json
│   └── agresores.json
├── css/
│   └── estilos.css
└── js/
    └── validaciones.js       # Opcional
```

`funciones.php` es un elemento propuesto para organizar el proyecto; puede adaptarse a los requisitos concretos de la asignatura. La estructura debe respetar los directorios obligatorios que indique el enunciado.

## Almacenamiento y relaciones

La aplicación utilizará tres archivos JSON independientes:

- `datos/victimas.json`: registros de víctimas.
- `datos/agresores.json`: registros de presuntos agresores.
- `datos/incidencias.json`: incidencias y referencias a la víctima y al agresor relacionados.

Cada registro tendrá un identificador único. Las incidencias guardarán los identificadores de los registros relacionados en lugar de duplicar todos sus datos.

Los archivos JSON deben inicializarse con un array vacío (`[]`). PHP leerá y escribirá la información mediante funciones como `file_get_contents()` y `file_put_contents()`, según los requisitos del proyecto.

### Datos temporales y datos persistentes

- **Datos temporales:** se podrán transportar entre formularios mediante `POST` y un campo oculto que contenga JSON, siguiendo el patrón de los formularios de clase.
- **Datos persistentes:** los registros guardados se recuperarán desde los archivos JSON, incluso cuando el usuario vuelva a entrar en la aplicación más adelante.

El envío de datos entre páginas no sustituye al guardado permanente en los archivos.

## Navegación

La navegación será libre. El usuario podrá acceder a Inicio, Incidencias, Víctimas, Agresores, Valoración de riesgo e Informe final desde un menú común.

Los formularios utilizarán el método `POST` y botones para desplazarse entre páginas, conservando los datos temporales que corresponda. Cada sección permitirá consultar los registros existentes, además de introducir información nueva.

## Indicadores de riesgo propuestos

Para la práctica se plantean doce indicadores orientativos:

1. Existencia de amenazas graves.
2. Episodios previos de violencia.
3. Aumento reciente de la frecuencia o gravedad de la violencia.
4. Agresiones físicas con lesiones.
5. Amenazas de muerte.
6. Acoso, persecución o control persistente.
7. Incumplimiento de medidas de protección.
8. Acceso a armas o amenazas con armas.
9. Separación reciente o conflicto relacionado con ella.
10. Antecedentes de violencia hacia otras personas.
11. Consumo problemático de alcohol u otras sustancias asociado a episodios violentos.
12. Amenazas o violencia que afecten a menores.

Cada indicador permitirá seleccionar «Sí», «No» o «Desconocido». La documentación de referencia deberá identificarse en la memoria del proyecto. Un recuento simple de respuestas positivas, si se utiliza, será una regla didáctica propia y no una valoración policial oficial.

## Validación y seguridad

Aunque sea un proyecto académico, se deben tener en cuenta estas medidas:

- Validar en PHP todos los campos obligatorios.
- Escapar correctamente los datos al mostrarlos en HTML, por ejemplo con `htmlspecialchars()`.
- Comprobar que la víctima y el agresor seleccionados existen.
- Generar identificadores únicos y evitar duplicados.
- Comprobar los errores de lectura y escritura de los archivos JSON.
- Evitar el acceso público directo a la carpeta `datos/`.
- Tener en cuenta posibles conflictos si varios procesos escriben simultáneamente en los archivos.
- Utilizar exclusivamente datos ficticios para las pruebas.

Los datos sobre violencia de género son sensibles. Una aplicación académica que utiliza archivos JSON no debe utilizarse como sistema policial real ni para almacenar datos personales reales.

## Orden recomendado de desarrollo

1. Crear las carpetas y los archivos iniciales.
2. Diseñar la página de inicio y el menú de navegación.
3. Probar la navegación y la conservación de datos mediante `POST` y JSON.
4. Implementar el registro, guardado y listado de víctimas.
5. Implementar el registro, guardado y listado de presuntos agresores.
6. Implementar el registro de incidencias y sus relaciones.
7. Añadir los indicadores de riesgo y guardar sus respuestas.
8. Generar el informe final a partir de los datos almacenados.
9. Probar la navegación, las consultas, las modificaciones, los errores y las relaciones entre registros.

Se recomienda comprobar cada apartado antes de continuar con el siguiente.

## Presentación y documentación

La memoria o explicación de la entrega debería describir:

- El objetivo y las funcionalidades.
- La estructura de directorios.
- El modelo de datos y las relaciones entre archivos JSON.
- Las funciones PHP utilizadas para leer y guardar información.
- La navegación entre formularios.
- Los indicadores seleccionados y su fuente documental.
- El método de valoración y sus limitaciones.
- Las pruebas realizadas y las medidas de seguridad incorporadas.

La página `index.php` deberá incluir los nombres de todos los integrantes del equipo, tal como solicite el enunciado.

## Tecnologías previstas

- **PHP:** lógica de los formularios, navegación y gestión de datos.
- **HTML:** estructura de las páginas y formularios.
- **JSON:** almacenamiento de los registros.
- **CSS:** presentación visual.

## Estado del proyecto

Este documento recoge el planteamiento inicial. La aplicación deberá desarrollarse y probarse progresivamente siguiendo los requisitos de la asignatura.
