<?php
include("conexion.php");

$query = "
SELECT 
v.id,
ao.ciudad AS origen,
ad.ciudad AS destino,
v.precio_base
FROM vuelos v
JOIN aeropuertos ao 
ON v.aeropuerto_origen = ao.id
JOIN aeropuertos ad 
ON v.aeropuerto_destino = ad.id
";

$resultado = pg_query($conexion, $query);
?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
SIGETUR
</title>

<link
rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">

<style>

body{
background:#f5f5f5;
margin:0;
padding:0;
display:flex;
flex-direction:column;
min-height:100vh;
}

/* ===== HEADER ===== */

nav{
background:#29b6f6;
height:80px;
line-height:80px;
}

.brand-logo{
font-size:42px !important;
font-weight:600;
left:50%;
transform:translateX(-50%);
position:absolute;
}

/* ===== CONTENIDO ===== */

main{
flex:1 0 auto;
padding-bottom:30px;
}

.titulo{
font-size:70px;
font-weight:700;
margin-top:40px;
margin-bottom:40px;
line-height:1.1;
color:#212121;
word-break:break-word;
}

/* ===== AVIÓN ===== */

.avion{
font-size:65px;
display:inline-block;
vertical-align:middle;
margin-right:10px;
}

/* ===== GRID ===== */

.grid-vuelos{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(300px,1fr));
gap:35px;
}

/* ===== CARD ===== */

.card-vuelo{
background:white;
border-radius:25px;
padding:30px;
box-shadow:0 5px 15px rgba(0,0,0,0.2);
transition:0.3s;
display:flex;
flex-direction:column;
justify-content:space-between;
min-height:320px;
}

.card-vuelo:hover{
transform:translateY(-5px);
}

.ruta{
font-size:34px;
font-weight:500;
line-height:1.2;
margin-bottom:30px;
word-break:break-word;
}

.precio{
font-size:42px;
font-weight:bold;
color:#03a9f4;
margin-bottom:40px;
}

.btn-comprar{
background:#29b6f6;
width:100%;
border-radius:6px;
height:55px;
line-height:55px;
font-size:28px;
}

.btn-comprar:hover{
background:#03a9f4;
}

/* ===== FOOTER ===== */

.page-footer{
background:#29b6f6;
padding-top:15px;
padding-bottom:15px;
}

/* ===== RESPONSIVE ===== */

@media(max-width:768px){

.brand-logo{
font-size:30px !important;
}

.titulo{
font-size:42px;
margin-top:25px;
}

.avion{
font-size:42px;
display:block;
margin-bottom:10px;
margin-right:0;
}

.ruta{
font-size:28px;
}

.precio{
font-size:32px;
}

.btn-comprar{
font-size:22px;
}

.card-vuelo{
min-height:280px;
padding:25px;
}

}

</style>

</head>

<body>

<!-- HEADER -->

<nav>

<div class="nav-wrapper">

<a href="#" class="brand-logo center">
SIGETUR
</a>

</div>

</nav>

<!-- CONTENIDO -->

<main>

<div class="container">

<h1 class="titulo">

<span class="avion">
✈️
</span>

Selecciona tu destino

</h1>

<div class="grid-vuelos">

<?php while($vuelo = pg_fetch_assoc($resultado)) { ?>

<div class="card-vuelo">

<div>

<div class="ruta">

<?php
echo htmlspecialchars($vuelo['origen']);
?>

→

<?php
echo htmlspecialchars($vuelo['destino']);
?>

</div>

<div class="precio">

$<?php
echo number_format($vuelo['precio_base'],2);
?>

</div>

</div>

<form
action="comprar.php"
method="POST">

<input
type="hidden"
name="vuelo_id"
value="<?php echo $vuelo['id']; ?>">

<button
type="submit"
class="btn btn-comprar">

COMPRAR

</button>

</form>

</div>

<?php } ?>

</div>

</div>

</main>

<!-- FOOTER -->

<footer class="page-footer">

<div class="container">
© 2026 SIGETUR
</div>

</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>

</body>
</html>