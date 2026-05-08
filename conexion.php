<?php

$host = getenv("PGHOST");
$port = getenv("PGPORT");
$db = getenv("PGDATABASE");
$user = getenv("PGUSER");
$password = getenv("PGPASSWORD");

$conexion = pg_connect("
host=$host
port=$port
dbname=$db
user=$user
password=$password
sslmode=require
options='endpoint=ep-summer-smoke-apzbn57v'
");

if (!$conexion) {
    die("Error de conexión");
}

?>