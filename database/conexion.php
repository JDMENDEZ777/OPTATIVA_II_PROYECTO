<?php

$host = "localhost";
$port = "5432";
$dbname = "sistema_login";
$user = "postgres";
$password = "trujillo30";

try {
    $conexion = new PDO("pgsql:host=$host;port=$port;dbname=$dbname", $user, $password);

    // Activar errores PDO
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // echo "Conexión exitosa";

} catch (PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}

?>