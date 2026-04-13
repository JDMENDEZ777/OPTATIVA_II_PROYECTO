<?php
session_start();
require_once("../database/conexion.php");

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $password = trim($_POST["password"]);
    $confirmar = trim($_POST["confirmar"]);

    // 1. Campos vacíos
    if(empty($username) || empty($email) || empty($password) || empty($confirmar)){
        $_SESSION["mensaje"] = "Todos los campos son obligatorios.";
        header("Location: ../frontend/registro.php");
        exit();
    }

    // 2. Usuario solo letras
    if(!preg_match("/^[A-Za-z]{1,30}$/", $username)){
        $_SESSION["mensaje"] = "Usuario inválido.";
        header("Location: ../frontend/registro.php");
        exit();
    }

    // 3. Email válido
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $_SESSION["mensaje"] = "Correo inválido.";
        header("Location: ../frontend/registro.php");
        exit();
    }

    // 4. Password mínimo 6
    if(strlen($password) < 6){
        $_SESSION["mensaje"] = "La contraseña debe tener mínimo 6 caracteres.";
        header("Location: ../frontend/registro.php");
        exit();
    }

   

     // 5. VALIDACIÓN DE CONTRASEÑA 
    if(!preg_match("/^[A-Za-z0-9]{6,30}$/", $password)){
        $_SESSION["mensaje"] = "La contraseña debe tener entre 6 y 30 caracteres (solo letras y números).";
        header("Location: ../frontend/registro.php");
        exit();
    }


    // 6. Coincidencia
    if($password !== $confirmar){
        $_SESSION["mensaje"] = "Las contraseñas no coinciden.";
        header("Location: ../frontend/registro.php");
        exit();
    }

    // 7. Usuario repetido
    $sql = "SELECT * FROM usuarios WHERE username = :username";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(":username", $username);
    $stmt->execute();

    if($stmt->rowCount() > 0){
        $_SESSION["mensaje"] = "El usuario ya existe.";
        header("Location: ../frontend/registro.php");
        exit();
    }

    // 8. VALIDAR EMAIL REPETIDO
    $sql = "SELECT * FROM usuarios WHERE email = :email";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(":email", $email);
    $stmt->execute();

    if($stmt->rowCount() > 0){
        $_SESSION["mensaje"] = "El correo ya está registrado.";
        header("Location: ../frontend/registro.php");
        exit();
    }

    // INSERTAR (rol 2 = estudiante)
    $sql = "INSERT INTO usuarios (username, email, password, rol_id) 
            VALUES (:username, :email, :password, 2)";

    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(":username", $username);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":password", $password);

    if($stmt->execute()){
        $_SESSION["mensaje"] = "Registro exitoso.";
        header("Location: ../frontend/procesar.php");
    } else {
        $_SESSION["mensaje"] = "Error en el registro.";
        header("Location: ../frontend/registro.php");
    }

    exit();
}