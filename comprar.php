<?php
include("conexion.php");

if (!isset($_POST['vuelo_id'])) die("Error");

$vuelo_id = intval($_POST['vuelo_id']);

// vuelo
$query = "
SELECT v.id, ao.ciudad origen, ad.ciudad destino, v.precio_base
FROM vuelos v
JOIN aeropuertos ao ON v.aeropuerto_origen = ao.id
JOIN aeropuertos ad ON v.aeropuerto_destino = ad.id
WHERE v.id = $vuelo_id
";

$result = pg_query($conexion, $query);
$vuelo = pg_fetch_assoc($result);

if (!$vuelo) die("Vuelo no encontrado");

// aerolíneas
$aerolineas_query = "
SELECT id, nombre
FROM aerolineas
";

$aerolineas_result = pg_query($conexion, $aerolineas_query);
?>

<!DOCTYPE html>
<html>

<head>

<meta charset="UTF-8">

<meta
name="viewport"
content="width=device-width, initial-scale=1.0">

<title>
Compra
</title>

<link
rel="stylesheet"
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

.card-compra{
padding:25px;
margin-top:30px;
border-radius:12px;
}

.btn{
background:#29b6f6;
}

.btn:hover{
background:#03a9f4;
}

#mensajeCupon,
#mensajeTarjeta{
margin-top:20px;
}

/* ===== CORRECCIÓN DISPLAY TARJETA ===== */

#modalTarjeta .input-field{
margin-top:25px;
}

#modalTarjeta input{
font-size:18px !important;
position:relative;
z-index:2;
background:transparent;
}

#modalTarjeta label{
pointer-events:none;
}

#modalTarjeta input:focus + label,
#modalTarjeta input:not(:placeholder-shown) + label,
#modalTarjeta input.valid + label{
transform: translateY(-14px) scale(0.8) !important;
transform-origin: 0 0;
color:#29b6f6 !important;
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

<div class="card white card-compra z-depth-2">

<h4>
<?php echo $vuelo['origen']." → ".$vuelo['destino']; ?>
</h4>

<h5>
Precio Base:
$<?php echo $vuelo['precio_base']; ?>
</h5>

<form
action="procesar_compra.php"
method="POST"
id="formCompra">

<input
type="hidden"
name="vuelo_id"
value="<?php echo $vuelo_id; ?>">

<!-- NOMBRE -->
<div class="input-field">

<input
type="text"
name="nombre"
required>

<label>
Nombre completo
</label>

</div>

<!-- EMAIL -->
<div class="input-field">

<input
type="email"
name="email"
required>

<label>
Correo electrónico
</label>

</div>

<!-- TELEFONO -->
<div class="input-field">

<input
type="text"
name="telefono"
pattern="[0-9]{8}"
maxlength="8"
required>

<label>
Teléfono (8 dígitos)
</label>

</div>

<!-- AEROLINEA -->
<div class="input-field">

<select name="aerolinea_id" required>

<option value="" disabled selected>
Seleccione aerolínea
</option>

<?php while($a = pg_fetch_assoc($aerolineas_result)) { ?>

<option value="<?php echo $a['id']; ?>">

<?php echo htmlspecialchars($a['nombre']); ?>

</option>

<?php } ?>

</select>

<label>
Aerolínea
</label>

</div>

<!-- CHECK DESCUENTO -->
<p>

<label>

<input type="checkbox" id="check_descuento">

<span>
Posee descuento promocional
</span>

</label>

</p>

<!-- METODO -->
<div class="input-field">

<select
name="metodo_pago"
id="metodo_pago"
required>

<option value="" disabled selected>
Seleccione método
</option>

<option value="tarjeta">
Tarjeta
</option>

<option value="paypal">
PayPal
</option>

</select>

<label>
Método de pago
</label>

</div>

<!-- MODAL DESCUENTO -->
<div id="modalDescuento" class="modal">

<div class="modal-content">

<h5>
Validar cupón
</h5>

<div class="input-field">

<input
type="text"
name="codigo_descuento"
id="codigo_descuento">

<label>
Código promocional
</label>

</div>

<button
type="button"
class="btn"
id="validarCupon">

Validar descuento

</button>

<div id="mensajeCupon"></div>

</div>

<div class="modal-footer">

<a
href="#!"
class="modal-close btn grey">

Cerrar

</a>

</div>

</div>

<!-- MODAL TARJETA -->
<div id="modalTarjeta" class="modal">

<div class="modal-content">

<h5>
Datos de tarjeta
</h5>

<!-- NUMERO -->
<div class="input-field">

<input
id="numero_tarjeta"
name="numero_tarjeta"
maxlength="16"
pattern="[0-9]{16}"
placeholder=" ">

<label for="numero_tarjeta">
Número de tarjeta
</label>

</div>

<!-- NOMBRE -->
<div class="input-field">

<input
id="nombre_tarjeta"
name="nombre_tarjeta"
placeholder=" ">

<label for="nombre_tarjeta">
Nombre en tarjeta
</label>

</div>

<!-- FECHA -->
<div class="input-field">

<input
type="text"
name="fecha_exp"
id="fecha_exp"
maxlength="5"
placeholder="MM/AA"
pattern="(0[1-9]|1[0-2])\/[0-9]{2}">

<label class="active" for="fecha_exp">
Fecha expiración
</label>

</div>

<!-- PIN -->
<div class="input-field">

<input
type="password"
id="pin"
name="pin"
maxlength="4"
pattern="[0-9]{4}"
placeholder=" ">

<label for="pin">
PIN
</label>

</div>

<div id="mensajeTarjeta"></div>

</div>

<div class="modal-footer">

<button
type="button"
id="btnAceptarTarjeta"
class="btn">

Aceptar

</button>

</div>

</div>

<!-- BOTONES -->
<div
style="
display:flex;
gap:10px;
flex-wrap:wrap;
margin-top:25px;
">

<button
class="btn waves-effect waves-light">

Comprar

</button>

<a
href="index.php"
class="btn grey">

Regresar

</a>

<button
type="button"
id="abrirTarjeta"
class="btn blue"
style="display:none;">

Editar tarjeta

</button>

</div>

</form>

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

<script>

document.addEventListener('DOMContentLoaded', function() {

M.FormSelect.init(document.querySelectorAll('select'));

M.Modal.init(document.querySelectorAll('.modal'),{
dismissible:true
});

let cuponValido = true;

// abrir modal descuento
document.getElementById("check_descuento")
.addEventListener("change", function(){

if(this.checked){

M.Modal
.getInstance(
document.getElementById('modalDescuento')
)
.open();

}

});

// abrir modal tarjeta
document.getElementById("metodo_pago")
.addEventListener("change", function(){

if(this.value==="tarjeta"){

let modal =
M.Modal.getInstance(
document.getElementById('modalTarjeta')
);

modal.open();

// mostrar botón editar
document.getElementById("abrirTarjeta")
.style.display = "inline-block";

}

});

// REABRIR TARJETA
document.getElementById("abrirTarjeta")
.addEventListener("click", function(){

let modal =
M.Modal.getInstance(
document.getElementById('modalTarjeta')
);

modal.open();

});

// VALIDAR TARJETA
document.getElementById("btnAceptarTarjeta")
.addEventListener("click", function(){

let numero =
document.getElementById("numero_tarjeta")
.value.trim();

let nombre =
document.getElementById("nombre_tarjeta")
.value.trim();

let fecha =
document.getElementById("fecha_exp")
.value.trim();

let pin =
document.getElementById("pin")
.value.trim();

let mensaje =
document.getElementById("mensajeTarjeta");

mensaje.innerHTML = "";

// tarjeta
if(!/^[0-9]{16}$/.test(numero)){

mensaje.innerHTML =
"<div class='card-panel red white-text'>❌ Número de tarjeta inválido</div>";

return;

}

// nombre
if(nombre.length < 3){

mensaje.innerHTML =
"<div class='card-panel red white-text'>❌ Nombre inválido</div>";

return;

}

// fecha
if(!/^(0[1-9]|1[0-2])\/[0-9]{2}$/.test(fecha)){

mensaje.innerHTML =
"<div class='card-panel red white-text'>❌ Fecha inválida</div>";

return;

}

// pin
if(!/^[0-9]{4}$/.test(pin)){

mensaje.innerHTML =
"<div class='card-panel red white-text'>❌ PIN inválido</div>";

return;

}

// cerrar modal
let modal =
M.Modal.getInstance(
document.getElementById('modalTarjeta')
);

modal.close();

M.toast({
html:'✔ Tarjeta validada correctamente'
});

});

// FORMATO MM/AA
document.getElementById("fecha_exp")
.addEventListener("input", function(e){

let valor =
e.target.value.replace(/\D/g,'');

if(valor.length >= 3){

valor =
valor.substring(0,2)
+
"/"
+
valor.substring(2,4);

}

e.target.value = valor;

});

// VALIDAR CUPON
document.getElementById("validarCupon")
.addEventListener("click", async function(){

let codigo =
document.getElementById("codigo_descuento")
.value.trim();

let email =
document.querySelector('input[name="email"]')
.value.trim();

let mensaje =
document.getElementById("mensajeCupon");

mensaje.innerHTML = "";

cuponValido = false;

if(codigo === ""){

mensaje.innerHTML =
"<div class='card-panel red white-text'>❌ Ingrese un código</div>";

return;

}

try{

let response =
await fetch(
"validar_cupon.php?codigo=" +
encodeURIComponent(codigo) +
"&email=" +
encodeURIComponent(email)
);

let data = await response.json();

// incorrecto
if(data.estado === "incorrecto"){

mensaje.innerHTML =
"<div class='card-panel red white-text'>❌ Código incorrecto</div>";

cuponValido = false;

return;

}

// vencido
if(data.estado === "vencido"){

mensaje.innerHTML =
"<div class='card-panel orange white-text'>❌ Código vencido</div>";

cuponValido = false;

return;

}

// usado
if(data.estado === "usado"){

mensaje.innerHTML =
"<div class='card-panel red white-text'>❌ Cupón ya fue canjeado</div>";

cuponValido = false;

return;

}

// válido
if(data.estado === "valido"){

mensaje.innerHTML =
"<div class='card-panel green white-text'>✔ Código válido</div>";

cuponValido = true;

}

}catch(error){

mensaje.innerHTML =
"<div class='card-panel red white-text'>❌ Error al validar</div>";

cuponValido = false;

}

});

// impedir compra
document.getElementById("formCompra")
.addEventListener("submit", function(e){

let check =
document.getElementById("check_descuento");

if(check.checked && !cuponValido){

e.preventDefault();

M.toast({
html:'❌ Debe ingresar un cupón válido'
});

}

});

});

</script>

</body>
</html>