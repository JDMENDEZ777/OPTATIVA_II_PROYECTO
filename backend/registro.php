<?php
session_start();
require_once("../database/conexion.php");

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $username = trim($_POST["username"]);
    $email = trim($_POST["email"]);
    $rol = trim($_POST["rol"] ?? "");
    $password = trim($_POST["password"]);
    $confirmar = trim($_POST["confirmar"]);

    if(empty($username) || empty($email) || empty($rol) || empty($password) || empty($confirmar)){
        $_SESSION["mensaje"] = "Todos los campos son obligatorios.";
        header("Location: ../frontend/registro.php");
        exit();
    }

    if(!preg_match("/^[A-Za-z]{1,30}$/", $username)){
        $_SESSION["mensaje"] = "Usuario invalido.";
        header("Location: ../frontend/registro.php");
        exit();
    }

    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        $_SESSION["mensaje"] = "Correo invalido.";
        header("Location: ../frontend/registro.php");
        exit();
    }

    if(!in_array($rol, ["Estudiante", "Docente"])){
        $_SESSION["mensaje"] = "Rol invalido. No se permite crear administradores desde el registro.";
        header("Location: ../frontend/registro.php");
        exit();
    }

    if(!preg_match("/^[A-Za-z0-9]{6,30}$/", $password)){
        $_SESSION["mensaje"] = "La contrasena debe tener entre 6 y 30 caracteres, solo letras y numeros.";
        header("Location: ../frontend/registro.php");
        exit();
    }

    if($password !== $confirmar){
        $_SESSION["mensaje"] = "Las contrasenas no coinciden.";
        header("Location: ../frontend/registro.php");
        exit();
    }

    $sql = "SELECT id FROM usuarios WHERE username = :username";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(":username", $username);
    $stmt->execute();

    if($stmt->rowCount() > 0){
        $_SESSION["mensaje"] = "El usuario ya existe.";
        header("Location: ../frontend/registro.php");
        exit();
    }

    $sql = "SELECT id FROM usuarios WHERE email = :email";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(":email", $email);
    $stmt->execute();

    if($stmt->rowCount() > 0){
        $_SESSION["mensaje"] = "El correo ya esta registrado.";
        header("Location: ../frontend/registro.php");
        exit();
    }

    $sql = "SELECT id FROM roles WHERE nombre = :rol";
    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(":rol", $rol);
    $stmt->execute();
    $rolId = $stmt->fetchColumn();

    if(!$rolId){
        $_SESSION["mensaje"] = "El rol seleccionado no existe en la base de datos.";
        header("Location: ../frontend/registro.php");
        exit();
    }

    $passwordHash = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO usuarios (username, email, password, rol_id)
            VALUES (:username, :email, :password, :rol_id)";

    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(":username", $username);
    $stmt->bindParam(":email", $email);
    $stmt->bindParam(":password", $passwordHash);
    $stmt->bindParam(":rol_id", $rolId, PDO::PARAM_INT);

    if($stmt->execute()){
        $_SESSION["mensaje"] = "Registro exitoso.";
        header("Location: ../frontend/procesar.php");
    } else {
        $_SESSION["mensaje"] = "Error en el registro.";
        header("Location: ../frontend/registro.php");
    }

    exit();
}
