# Request Helpers

## Descripción

`request-helpers` es una librería PHP que proporciona funciones útiles para manejar requests de manera más sencilla y eficiente
con formatos JSON, formData, parametros, manejo de archivos y tokens.

### La libreria verifica y valida el tipo de metodo por si sola automatizando el proceso.

# Instalación

Para instalar `request-helpers`, puedes usar Composer. Ejecuta el siguiente comando en tu terminal:

```bash
composer require gabogalro/request-helpers
```

# Usos para Request::json;

## Ejemplo de json en POST, PUT o PATCH.

```json
{
  "NombreCliente": "Juan",
  "CorreoCliente": "juan@correo.com"
}
```

## Ejemplo de uso para manipular un JSON en POST, PUT O PATCH.

```php
<?php

use gabogalro\requestHelpers\Request;

public function registrarCliente(){

  $request = Request::json();

  $data = [
    //el nombre de la izquierda es la variable dentro del array, debe coincidir con el nombre que se envia en el json
    //el nombre de la derecha es el nombre del campo en el json
    'nombreCliente' => $request['NombreCliente'],
    'correoCliente' => $request['CorreoCliente']
  ]

  echo json_encode($data, JSON_PRETTY_PRINT);
}

```

## Respuesta esperada

```json
{
  "nombreCliente": "Juan",
  "correoCliente": "juan@correo.com"
}
```

## Ejemplo de uso para extraer un TOKEN en formato Bearer Token o JWT bearer.

```php
<?php

use gabogalro\requestHelpers\Request;

public function login(){

  //esto extrae el token de la cabecera Authorization: Bearer token
  //permitiendonos asi manipularlo como un string para destruir, iniciar o validar una sesion
  $token = Request::token();

  if($token){
    echo "token: $token";
  }
}
```

## Respuesta esperada

### Advertencia: el token no es real, es solo un ejemplo para mostrar el funcionamiento de la libreria.

```bash
  "token": "eyJhbGciMjM5MDIyfQ.Sf"
```

# Usos para Request::parameter;

## Ejemplo de uso para manipular un parametro, sin buscar valor especifico

```php
<?php

use gabogalro\requestHelpers\Request;

public function mifuncion(){
  //con esto podemos recibir parametros al endpoint, ejemplo {{url}}cliente/get?clienteId=1&&nombre=Juan
  //algun servicio o modelo
  $request = Request::parameter(); // -> devuelve un array asociativo
  // Acceder a cada valor individual
  //$clienteId = $request['clienteId'];
  //$nombre    = $request['nombre'];
  echo json_encode($request);
}

```

## Respuesta esperada

```json
{
  "clienteId": "1",
  "nombre": "Juan"
}
```

## Ejemplo de uso para manipular un parametro buscando un valor en especifico

```php
<?php

use gabogalro\requestHelpers\Request;

public function registrarCliente(){
  //con esto podemos recibir parametros al endpoint, ejemplo {{url}}cliente/get?clienteId=1&&nombre=Juan
  //algun servicio o modelo
  $request = Request::parameter('clienteId');
  echo $request;
}

```

## Respuesta esperada

```bash -> ya no devuelve un array asociativo, devuelve un valor en especifico
1 -> esto se puede guardar en una variable y usarla como se desee. Ejemplo $clienteId = Request::parameter('clienteId');
```

# Usos para Request::formData;

# NOTA IMPORTANTE: solo funciona con POST; PUT o PATCH ESTAN DESHABILITADOS YA QUE PHP NATIVO NO SOPORTA PUT/PATCH EN FORM DATA

## Ejemplo form data

```html
<form>
  <input type="text" name="nombre" value="Juan" />
  <input type="email" name="domicilio" value="domicilio" />
  <input type="text" name="puesto" value="puesto" />
</form>
```

## Ejemplo de uso para manipular el formulario anterior en forma plana.

```php
<?php

use gabogalro\requestHelpers\Request;

public function registrarCliente(){
  $request = Request::formData();
  echo json_encode($request, JSON_PRETTY_PRINT);
}

```

## Respuesta esperada

```json
{
  "nombre": "pepe",
  "puesto": "empleado",
  "domicilios": "domicilio"
}
```

## Ejemplo de uso para manipular un formData con datos anidados

```php
<?php

use gabogalro\requestHelpers\Request;

//ejemplo 1
public function registrarCliente(){
  // [
  //  'name' => 'pepe',
  //  'domicilios' => ['dom1', 'dom2'],
  //  'puesto' => 'empleado'
  // ]
  $request = Request::formData();
  $dataEmpleado = [
    'nombre' => $request['nombre'],
    'puesto' => $request['puesto'],
    'domicilios' => []
  ];
  foreach($request['domicilios'] as $domicilio){
    $dataEmpleado['domicilios'][] = $domicilio;
  }
   echo json_encode($dataEmpleado);
}

//ejemplo 2
use gabogalro\requestHelpers\Request;

public function registrarCliente() {
    $request = Request::formData();

    $dataEmpleado = [
        'nombre' => $request['nombre'],
        'puesto' => $request['puesto'],
        'domicilios' => $request['domicilios'] ?? []
    ];

    echo json_encode($dataEmpleado);
}

```

## Respuesta esperada

```json
{
  "nombre": "pepe",
  "puesto": "empleado",
  "domicilios": ["dom1", "dom2"]
}
```

## Ejemplo de uso para manipular un formData que viene en forma de object

```php
<?php

use gabogalro\requestHelpers\Request;

public function registrarCliente(){
    //   'empleados' => [
    //     [
    //         'name[0]' => 'pepe1',
    //         'domicilio[0]' => 'dom1',
    //         'puesto[0]' => 'empleado1',
    //     ],
    //     [
    //         'name[1]' => 'pepe2',
    //         'domicilio[1]' => 'dom2',
    //         'puesto[1]' => 'empleado2',
    //     ],
    //     [
    //         'name[2]' => 'pepe3',
    //         'domicilio[2]' => 'dom3',
    //         'puesto[2]' => 'empleado3',
    //     ],
    // ]
  $data = Request::formData();
  $empleados = [];
  foreach($data['empleados'] ?? [] as $empleado)
  {
    $empleados[] =
    [
      "nombre" => $empleado['name'] ?? null,
      "domicilio" => $empleado['domicilio'] ?? null,
      "puesto" => $empleado['puesto'] ?? null
    ];
    //ejemplo de uso en un procedimiento almacenado usando la libreria sql-helpers
    //DB::statement('exec sp_registrar_empleado ?, ?, ?'),$empleados;
  }
  json_encode($empleados);
}

```

## Respuesta esperada

```json
[
  {
    "nombre": "pepe1",
    "domicilio": "dom1",
    "puesto": "empleado1"
  },
  {
    "nombre": "pepe2",
    "domicilio": "dom2",
    "puesto": "empleado2"
  },
  {
    "nombre": "pepe3",
    "domicilio": "dom3",
    "puesto": "empleado3"
  }
]
```

## Requisitos previos

- PHP 5.4 o superior
- Composer

## License

MIT © gabogalro. See [LICENSE](LICENSE) for details.
