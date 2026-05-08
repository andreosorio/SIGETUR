<?php
include("conexion.php");

$query = "
SELECT 
    v.id,
    ao.ciudad AS origen,
    ad.ciudad AS destino,
    v.precio_base
FROM vuelos v
JOIN aeropuertos ao ON v.aeropuerto_origen = ao.id
JOIN aeropuertos ad ON v.aeropuerto_destino = ad.id
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
display:flex;
min-height:100vh;
flex-direction:column;
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

.titulo{
margin-top:30px;
margin-bottom:30px;
font-weight:600;
}

.grid-vuelos{
display:grid;
grid-template-columns:repeat(auto-fit,minmax(320px,1fr));
gap:25px;
margin-bottom:40px;
}

.card-vuelo{
border-radius:15px;
padding:15px;
height:100%;
display:flex;
flex-direction:column;
justify-content:space-between;
}

.card-vuelo h5{
font-size:32px;
margin-bottom:25px;
}

.precio{
font-size:22px;
font-weight:bold;
margin-bottom:20px;
color:#03a9f4;
}

.btn-comprar{
background:#29b6f6;
width:100%;
}

.btn-comprar:hover{
background:#03a9f4;
}

@media(max-width:600px){

.card-vuelo h5{
font-size:24px;
}

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

<h3 class="titulo">
Selecciona tu destino
</h3>

<!-- GRID -->
<div class="grid-vuelos">

<?php while($fila = pg_fetch_assoc($resultado)) { ?>

<div class="card white z-depth-2 card-vuelo">

<div>

<h5>
<?php echo htmlspecialchars($fila['origen']); ?>
→
<?php echo htmlspecialchars($fila['destino']); ?>
</h5>

<p class="precio">
$<?php echo number_format($fila['precio_base'],2); ?>
</p>

</div>

<form action="comprar.php" method="POST">

<input
type="hidden"
name="vuelo_id"
value="<?php echo $fila['id']; ?>">

<button
type="submit"
class="btn waves-effect waves-light btn-comprar">

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

© 2026 SIGETUR - Sistema de compra de vuelos

</div>

</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>

</body>
</html>