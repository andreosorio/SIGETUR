<?php
include("conexion.php");

$vuelo_id = intval($_POST['vuelo_id']);
$nombre = trim($_POST['nombre']);
$email = trim($_POST['email']);
$telefono = trim($_POST['telefono']);
$metodo_pago = $_POST['metodo_pago'];
$aerolinea_id = $_POST['aerolinea_id'];
$codigo_descuento = trim($_POST['codigo_descuento'] ?? "");

// =======================
// VALIDAR TELÉFONO
// =======================

if (!preg_match('/^[0-9]{8}$/', $telefono)) {

    die("❌ Teléfono inválido");

}

// =======================
// VALIDAR AEROLÍNEA
// =======================

$res = pg_query_params(

    $conexion,

    "SELECT id
    FROM aerolineas
    WHERE id = $1",

    [$aerolinea_id]
);

if (pg_num_rows($res) == 0) {

    die("❌ Aerolínea inválida");

}

// =======================
// BUSCAR USUARIO
// =======================

$res = pg_query_params(

    $conexion,

    "SELECT id, total_compras
    FROM usuarios
    WHERE email = $1",

    [$email]
);

if (pg_num_rows($res) > 0) {

    $row = pg_fetch_assoc($res);

    $usuario_id = $row['id'];
    $total_compras = $row['total_compras'];

} else {

    // CREAR USUARIO
    $res = pg_query_params(

        $conexion,

        "INSERT INTO usuarios
        (
            nombre,
            email,
            telefono,
            total_compras
        )

        VALUES ($1,$2,$3,0)

        RETURNING id",

        [$nombre,$email,$telefono]
    );

    $usuario_id = pg_fetch_result($res,0,0);

    $total_compras = 0;
}

// =======================
// OBTENER PRECIO BASE
// =======================

$res = pg_query_params(

    $conexion,

    "SELECT precio_base
    FROM vuelos
    WHERE id = $1",

    [$vuelo_id]
);

$precio = pg_fetch_result($res,0,0);

$nueva_compra = $total_compras + 1;

// =======================
// MENSAJES
// =======================

$mensaje_cupon = "";
$mensaje_descuento = "";
$mensaje_fidelidad = "";

// =======================
// CUPÓN
// =======================

if ($codigo_descuento != "") {

    // buscar cupón
    $res = pg_query_params(

        $conexion,

        "SELECT porcentaje, fecha_expiracion
        FROM descuentos
        WHERE codigo = $1",

        [$codigo_descuento]
    );

    // ❌ código incorrecto
    if (pg_num_rows($res) == 0) {

        die("❌ Código incorrecto");

    }

    $row = pg_fetch_assoc($res);

    $hoy = date("Y-m-d");

    // ❌ cupón vencido
    if ($row['fecha_expiracion'] < $hoy) {

        die("❌ Código vencido");

    }

    // ❌ cupón ya usado
    $resUso = pg_query_params(

        $conexion,

        "SELECT id
        FROM cupones_usados
        WHERE usuario_id = $1
        AND codigo = $2",

        [$usuario_id, $codigo_descuento]
    );

    if (pg_num_rows($resUso) > 0) {

        die("❌ Cupón ya fue canjeado");

    }

    // ✅ aplicar descuento
    $descuento =
    $precio * $row['porcentaje'] / 100;

    $precio -= $descuento;

    $mensaje_descuento =
    "✔ Descuento aplicado correctamente";

    // guardar cupón usado
    pg_query_params(

        $conexion,

        "INSERT INTO cupones_usados
        (
            usuario_id,
            codigo
        )

        VALUES ($1,$2)",

        [$usuario_id, $codigo_descuento]
    );
}

// =======================
// CLIENTE FRECUENTE
// =======================

else if ($nueva_compra % 5 == 0) {

    $descuento =
    $precio * 0.20;

    $precio -= $descuento;

    $mensaje_descuento =
    "🎉 Cliente frecuente: 20% aplicado";
}

// =======================
// MENSAJE PRÓXIMO DESCUENTO
// =======================

if (($nueva_compra + 1) % 5 == 0) {

    $mensaje_fidelidad =
    "🎉 Felicidades, en tu próximo vuelo tienes un 20% de descuento";

}

// =======================
// INSERTAR BOLETO
// =======================

pg_query_params(

    $conexion,

    "INSERT INTO boletos
    (
        usuario_id,
        vuelo_id,
        precio_pagado,
        aerolinea_id,
        codigo_descuento
    )

    VALUES ($1,$2,$3,$4,$5)",

    [
        $usuario_id,
        $vuelo_id,
        $precio,
        $aerolinea_id,
        $codigo_descuento
    ]
);

// =======================
// ACTUALIZAR COMPRAS
// =======================

pg_query_params(

    $conexion,

    "UPDATE usuarios
    SET total_compras = total_compras + 1
    WHERE id = $1",

    [$usuario_id]
);

// =======================
// GUARDAR TARJETA
// =======================

if ($metodo_pago == "tarjeta") {

    $ultimos4 =
    substr($_POST['numero_tarjeta'], -4);

    $pin_hash =
    password_hash($_POST['pin'], PASSWORD_DEFAULT);

    pg_query_params(

        $conexion,

        "INSERT INTO tarjetas
        (
            usuario_id,
            ultimos4,
            nombre_tarjeta,
            fecha_exp,
            pin_hash
        )

        VALUES ($1,$2,$3,$4,$5)",

        [
            $usuario_id,
            $ultimos4,
            $_POST['nombre_tarjeta'],
            $_POST['fecha_exp'],
            $pin_hash
        ]
    );
}
?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<title>
Compra Exitosa
</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">

<style>

body{
display:flex;
min-height:100vh;
flex-direction:column;
background:#f5f5f5;
}

main{
flex:1 0 auto;
}

nav{
background:#29b6f6;
}

.page-footer{
background:#29b6f6;
}

.card-success{
padding:30px;
margin-top:40px;
border-radius:12px;
}

.btn{
background:#29b6f6;
}

.btn:hover{
background:#03a9f4;
}

</style>

</head>

<body>

<!-- HEADER -->
<nav>

<div class="nav-wrapper container">

<a href="#" class="brand-logo">
SIGETUR ✈️
</a>

</div>

</nav>

<main>

<div class="container">

<div class="card white card-success z-depth-2">

<h4>
Compra realizada ✈️
</h4>

<p>

Gracias por tu compra,

<strong>
<?php echo htmlspecialchars($nombre); ?>
</strong>

</p>

<h5>

Precio final:

$<?php echo number_format($precio,2); ?>

</h5>

<!-- DESCUENTO -->
<?php if($mensaje_descuento) { ?>

<div class="card-panel green lighten-2 white-text">

<?php echo $mensaje_descuento; ?>

</div>

<?php } ?>

<!-- FIDELIDAD -->
<?php if($mensaje_fidelidad) { ?>

<div class="card-panel blue lighten-2 white-text">

<?php echo $mensaje_fidelidad; ?>

</div>

<?php } ?>

<br>

<a href="index.php"
class="btn waves-effect waves-light">

Volver al inicio

</a>

</div>

</div>

</main>

<!-- FOOTER -->
<footer class="page-footer">

<div class="container">
© 2026 SIGETUR - Sistema de compra de vuelos
</div>

</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>

</body>
</html>