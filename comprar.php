<?php
include("conexion.php");

if (!isset($_POST['vuelo_id'])) {
    die("Error");
}

$vuelo_id = intval($_POST['vuelo_id']);

// vuelo
$query = "
SELECT 
    v.id,
    ao.ciudad AS origen,
    ad.ciudad AS destino,
    v.precio_base
FROM vuelos v
JOIN aeropuertos ao ON v.aeropuerto_origen = ao.id
JOIN aeropuertos ad ON v.aeropuerto_destino = ad.id
WHERE v.id = $vuelo_id
";

$result = pg_query($conexion, $query);
$vuelo = pg_fetch_assoc($result);

if (!$vuelo) {
    die("Vuelo no encontrado");
}

// aerolíneas
$aerolineas_query = "SELECT id, nombre FROM aerolineas";
$aerolineas_result = pg_query($conexion, $aerolineas_query);
?>

<!DOCTYPE html>
<html lang="es">

<head>

<meta charset="UTF-8">

<title>Comprar boleto</title>

<link rel="stylesheet"
href="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/css/materialize.min.css">

<style>

body{
    background:#f5f5f5;
}

nav{
    background:#29b6f6;
}

.page-footer{
    background:#29b6f6;
}

.container{
    margin-top:30px;
}

.card-panel{
    border-radius:15px;
}

.modal{
    border-radius:15px;
}

input{
    overflow:hidden !important;
}

</style>

</head>

<body>

<!-- HEADER -->

<nav>
    <div class="nav-wrapper container">
        <a href="index.php" class="brand-logo">
            SIGETUR
        </a>
    </div>
</nav>

<div class="container">

    <div class="card-panel white">

        <h4>
            <?php echo $vuelo['origen']; ?>
            →
            <?php echo $vuelo['destino']; ?>
        </h4>

        <h5>
            Precio:
            $<?php echo $vuelo['precio_base']; ?>
        </h5>

    </div>

    <!-- FORM -->

    <form action="procesar_compra.php" method="POST">

        <input
            type="hidden"
            name="vuelo_id"
            value="<?php echo $vuelo_id; ?>"
        >

        <!-- NOMBRE -->

        <div class="input-field">

            <input
                type="text"
                id="nombre"
                name="nombre"
                maxlength="80"
                required
            >

            <label for="nombre">
                Nombre completo
            </label>

        </div>

        <!-- EMAIL -->

        <div class="input-field">

            <input
                type="email"
                id="email"
                name="email"
                maxlength="120"
                required
            >

            <label for="email">
                Correo electrónico
            </label>

        </div>

        <!-- TELEFONO -->

        <div class="input-field">

            <input
                type="text"
                id="telefono"
                name="telefono"
                maxlength="8"
                pattern="[0-9]{8}"
                required
            >

            <label for="telefono">
                Teléfono
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

            <label>Aerolínea</label>

        </div>

        <!-- DESCUENTO -->

        <p>

            <label>

                <input
                    type="checkbox"
                    id="check_descuento"
                >

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
                required
            >

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

            <label>Método de pago</label>

        </div>

        <!-- BOTONES -->

        <div style="margin-top:25px;">

            <button
                type="submit"
                class="btn light-blue"
            >
                Comprar
            </button>

            <a
                href="index.php"
                class="btn grey"
            >
                Regresar
            </a>

            <button
                type="button"
                class="btn orange"
                id="editarTarjeta"
            >
                Editar tarjeta
            </button>

        </div>

        <!-- MODAL DESCUENTO -->

        <div
            id="modalDescuento"
            class="modal"
        >

            <div class="modal-content">

                <h5>
                    Código promocional
                </h5>

                <div class="input-field">

                    <input
                        type="text"
                        name="codigo_descuento"
                        id="codigo_descuento"
                        maxlength="30"
                    >

                    <label for="codigo_descuento">
                        Código
                    </label>

                </div>

                <button
                    type="button"
                    class="btn blue"
                    id="validarCupon"
                >
                    Validar cupón
                </button>

                <div
                    id="mensajeCupon"
                    style="margin-top:20px;font-weight:bold;"
                ></div>

            </div>

        </div>

        <!-- MODAL TARJETA -->

        <div
            id="modalTarjeta"
            class="modal"
        >

            <div class="modal-content">

                <h5>
                    Datos de tarjeta
                </h5>

                <!-- NUMERO -->

                <div class="input-field">

                    <input
                        type="text"
                        id="numero_tarjeta"
                        name="numero_tarjeta"
                        maxlength="19"
                        autocomplete="off"
                    >

                    <label
                        for="numero_tarjeta"
                        class="active"
                    >
                        Número de tarjeta
                    </label>

                </div>

                <!-- NOMBRE -->

                <div class="input-field">

                    <input
                        type="text"
                        id="nombre_tarjeta"
                        name="nombre_tarjeta"
                        maxlength="50"
                    >

                    <label
                        for="nombre_tarjeta"
                    >
                        Nombre en tarjeta
                    </label>

                </div>

                <!-- FECHA -->

                <div class="input-field">

                    <input
                        type="text"
                        id="fecha_exp"
                        name="fecha_exp"
                        maxlength="5"
                        placeholder="MM/AA"
                    >

                    <label
                        for="fecha_exp"
                        class="active"
                    >
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
                    >

                    <label
                        for="pin"
                    >
                        PIN
                    </label>

                </div>

            </div>

            <div class="modal-footer">

                <button
                    type="button"
                    class="btn blue"
                    id="btnAceptarTarjeta"
                >
                    Aceptar
                </button>

                <button
                    type="button"
                    class="btn grey modal-close"
                >
                    Cancelar
                </button>

            </div>

        </div>

    </form>

</div>

<!-- FOOTER -->

<footer class="page-footer">

    <div class="container">
        © 2026 SIGETUR
    </div>

</footer>

<!-- JS -->

<script src="https://cdnjs.cloudflare.com/ajax/libs/materialize/1.0.0/js/materialize.min.js"></script>

<script>

document.addEventListener('DOMContentLoaded', function() {

    M.FormSelect.init(document.querySelectorAll('select'));

    M.Modal.init(document.querySelectorAll('.modal'),{
        dismissible:true
    });

    // TELEFONO SOLO NUMEROS

    document.getElementById("telefono")
    .addEventListener("input", function(e){

        e.target.value = e.target.value
            .replace(/\D/g,'')
            .substring(0,8);

    });

    // ABRIR TARJETA

    document.getElementById("metodo_pago")
    .addEventListener("change", function(){

        if(this.value === "tarjeta"){

            M.Modal.getInstance(
                document.getElementById("modalTarjeta")
            ).open();

        }

    });

    // REABRIR TARJETA

    document.getElementById("editarTarjeta")
    .addEventListener("click", function(){

        M.Modal.getInstance(
            document.getElementById("modalTarjeta")
        ).open();

    });

    // FORMATO TARJETA

    const tarjeta = document.getElementById("numero_tarjeta");

    tarjeta.addEventListener("input", function(e){

        let valor = e.target.value;

        valor = valor.replace(/\D/g,'');

        valor = valor.substring(0,16);

        valor = valor.replace(/(.{4})/g, '$1 ').trim();

        e.target.value = valor;

    });

    // FECHA

    const fecha = document.getElementById("fecha_exp");

    fecha.addEventListener("input", function(e){

        let valor = e.target.value;

        valor = valor.replace(/\D/g,'');

        if(valor.length >= 3){

            valor =
                valor.substring(0,2)
                + "/"
                + valor.substring(2,4);

        }

        e.target.value = valor;

    });

    // PIN SOLO NUMEROS

    document.getElementById("pin")
    .addEventListener("input", function(e){

        e.target.value = e.target.value
            .replace(/\D/g,'')
            .substring(0,4);

    });

    // VALIDAR TARJETA

    document.getElementById("btnAceptarTarjeta")
    .addEventListener("click", function(){

        const numero =
            document.getElementById("numero_tarjeta").value;

        const nombre =
            document.getElementById("nombre_tarjeta").value;

        const fecha =
            document.getElementById("fecha_exp").value;

        const pin =
            document.getElementById("pin").value;

        if(
            numero.length < 19 ||
            nombre.trim() === "" ||
            fecha.length < 5 ||
            pin.length < 4
        ){

            M.toast({
                html:'Complete correctamente la tarjeta'
            });

            return;

        }

        M.Modal.getInstance(
            document.getElementById("modalTarjeta")
        ).close();

        M.toast({
            html:'Tarjeta validada correctamente'
        });

    });

    // DESCUENTO

    document.getElementById("check_descuento")
    .addEventListener("change", function(){

        if(this.checked){

            M.Modal.getInstance(
                document.getElementById("modalDescuento")
            ).open();

        }

    });

});

</script>

</body>
</html>