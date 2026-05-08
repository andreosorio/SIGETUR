<?php

$host = getenv("PGHOST");
$port = getenv("PGPORT");
$db = getenv("PGDATABASE");
$user = getenv("PGUSER");
$password = getenv("PGPASSWORD");

echo "HOST: $host <br>";
echo "PORT: $port <br>";
echo "DB: $db <br>";
echo "USER: $user <br>";

$conexion = pg_connect("
host=$host
port=$port
dbname=$db
user=$user
password=$password
sslmode=require
options='endpoint=ep-summer-smoke-apzbn57v'
");

if ($conexion) {
    echo "<h2>Conexión exitosa ✅</h2>";
} else {
    echo "<h2>Error conexión ❌</h2>";
}
?>