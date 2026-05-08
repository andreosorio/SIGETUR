<?php
$conexion = pg_connect("
    host=ep-summer-smoke-apzbn57v.c-7.us-east-1.aws.neon.tech
    dbname=neondb
    user=neondb_owner
    password=npg_fJqByZenL97Y
    sslmode=require
    options='endpoint=ep-summer-smoke-apzbn57v'
");

if (!$conexion) {
    die("Error de conexión");
}
?>