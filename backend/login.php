<?php
session_start();
require_once("../database/conexion.php");

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);


    if(empty($username)){
        $_SESSION["mensaje"] = "El usuario está vacío.";
        header("Location: ../frontend/procesar.php");
        exit();
    }

    if(empty($password)){
        $_SESSION["mensaje"] = "La contraseña está vacía.";
        header("Location: ../frontend/procesar.php");
        exit();
    }

    $sql = "SELECT u.*, r.nombre AS rol_nombre
            FROM usuarios u
            INNER JOIN roles r ON u.rol_id = r.id
            WHERE u.username = :username";

    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(":username", $username);
    $stmt->execute();

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$usuario){
        $_SESSION["mensaje"] = "El usuario no existe.";
        header("Location: ../frontend/procesar.php");
        exit();
    }

    if($usuario["password"] !== $password){
        $_SESSION["mensaje"] = "Contraseña incorrecta.";
        header("Location: ../frontend/procesar.php");
        exit();
    }

    $_SESSION["usuario"] = $usuario["username"];
    $_SESSION["rol"] = $usuario["rol_nombre"];
    
    unset($username, $password);
    header("Location: ../frontend/dashboard.php");
  
    exit();
}