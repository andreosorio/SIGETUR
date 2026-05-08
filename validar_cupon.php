<?php

include("conexion.php");

header('Content-Type: application/json');

$codigo = trim($_GET['codigo'] ?? '');
$email = trim($_GET['email'] ?? '');

// ❌ código vacío
if($codigo == ""){

echo json_encode([
"estado" => "incorrecto"
]);

exit;

}

// buscar usuario
$usuario_id = null;

if($email != ""){

$resUsuario = pg_query_params(

$conexion,

"SELECT id
FROM usuarios
WHERE email = $1",

[$email]

);

if(pg_num_rows($resUsuario) > 0){

$rowUsuario = pg_fetch_assoc($resUsuario);

$usuario_id = $rowUsuario['id'];

}

}

// buscar cupón
$res = pg_query_params(

$conexion,

"SELECT fecha_expiracion
FROM descuentos
WHERE codigo = $1",

[$codigo]

);

// ❌ código incorrecto
if(pg_num_rows($res) == 0){

echo json_encode([
"estado" => "incorrecto"
]);

exit;

}

$row = pg_fetch_assoc($res);

$hoy = date("Y-m-d");

// ❌ código vencido
if($row['fecha_expiracion'] < $hoy){

echo json_encode([
"estado" => "vencido"
]);

exit;

}

// ❌ cupón ya usado
if($usuario_id){

$resUso = pg_query_params(

$conexion,

"SELECT id
FROM cupones_usados
WHERE usuario_id = $1
AND codigo = $2",

[$usuario_id,$codigo]

);

if(pg_num_rows($resUso) > 0){

echo json_encode([
"estado" => "usado"
]);

exit;

}

}

// ✅ válido
echo json_encode([
"estado" => "valido"
]);