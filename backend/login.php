<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

require_once("../database/conexion.php");

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    // Usuario: solo letras, máximo 30
    if(!preg_match("/^[A-Za-z]{1,30}$/", $username)){
        $_SESSION["mensaje"] = "Correo o contraseña incorrecta";
        header("Location: ../frontend/procesar.php");
        exit();
    }

    // Contraseña: letras y números, máximo 30
    if(!preg_match("/^[A-Za-z0-9]{1,30}$/", $password)){
        $_SESSION["mensaje"] = "Correo o contraseña incorrecta";
        header("Location: ../frontend/procesar.php");
        exit();
    }

    // Buscar usuario
    $sql = "SELECT u.*, r.nombre AS rol_nombre
            FROM usuarios u
            INNER JOIN roles r ON u.rol_id = r.id
            WHERE u.username = :username";

    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(":username", $username);
    $stmt->execute();

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    // Validación segura (no revela qué falló)
    if(!$usuario || $usuario["password"] !== $password){
        $_SESSION["mensaje"] = "Correo o contraseña incorrecta";
        header("Location: ../frontend/procesar.php");
        exit();
    }

    // Login correcto
    $_SESSION["usuario"] = $usuario["username"];
    $_SESSION["rol"] = $usuario["rol_nombre"];

    header("Location: ../frontend/dashboard.php");
    exit();
}