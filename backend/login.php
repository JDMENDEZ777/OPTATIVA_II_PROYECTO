<?php
session_start();
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

require_once("../database/conexion.php");
require_once("auditoria.php");

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    if(!preg_match("/^[A-Za-z]{1,30}$/", $username)){
        $_SESSION["mensaje"] = "Correo o contrasena incorrecta";
        header("Location: ../frontend/procesar.php");
        exit();
    }

    if(!preg_match("/^[A-Za-z0-9]{1,30}$/", $password)){
        $_SESSION["mensaje"] = "Correo o contrasena incorrecta";
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

    if($usuario && $usuario["bloqueado"]){
        registrarAuditoria($conexion, $usuario["id"], $usuario["username"], "LOGIN_BLOQUEADO", "Usuario bloqueado intento ingresar");
        $_SESSION["mensaje"] = "Usuario bloqueado. Contacte al administrador.";
        header("Location: ../frontend/procesar.php");
        exit();
    }

    $passwordCorrecta = false;

    if($usuario){
        $hashInfo = password_get_info($usuario["password"]);
        if($hashInfo["algo"] !== 0){
            $passwordCorrecta = password_verify($password, $usuario["password"]);
        } else {
            $passwordCorrecta = $usuario["password"] === $password;
        }
    }

    if(!$usuario || !$passwordCorrecta){
        if($usuario){
            $intentos = ((int)$usuario["intentos_fallidos"]) + 1;
            $bloqueado = $intentos >= 3;

            $sqlIntentos = "UPDATE usuarios
                            SET intentos_fallidos = :intentos,
                                bloqueado = :bloqueado,
                                bloqueado_en = CASE WHEN :bloqueado = true THEN CURRENT_TIMESTAMP ELSE bloqueado_en END
                            WHERE id = :id";
            $stmtIntentos = $conexion->prepare($sqlIntentos);
            $stmtIntentos->bindParam(":intentos", $intentos, PDO::PARAM_INT);
            $stmtIntentos->bindParam(":bloqueado", $bloqueado, PDO::PARAM_BOOL);
            $stmtIntentos->bindParam(":id", $usuario["id"], PDO::PARAM_INT);
            $stmtIntentos->execute();

            registrarAuditoria($conexion, $usuario["id"], $usuario["username"], "LOGIN_FALLIDO", "Intento fallido numero " . $intentos);
        } else {
            registrarAuditoria($conexion, null, $username, "LOGIN_FALLIDO", "Usuario no encontrado");
        }

        $_SESSION["mensaje"] = "Correo o contrasena incorrecta";
        header("Location: ../frontend/procesar.php");
        exit();
    }

    $sqlAcceso = "UPDATE usuarios
                  SET intentos_fallidos = 0,
                      ultimo_acceso = CURRENT_TIMESTAMP
                  WHERE id = :id";
    $stmtAcceso = $conexion->prepare($sqlAcceso);
    $stmtAcceso->bindParam(":id", $usuario["id"], PDO::PARAM_INT);
    $stmtAcceso->execute();

    if(password_get_info($usuario["password"])["algo"] === 0){
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $sqlHash = "UPDATE usuarios SET password = :password WHERE id = :id";
        $stmtHash = $conexion->prepare($sqlHash);
        $stmtHash->bindParam(":password", $passwordHash);
        $stmtHash->bindParam(":id", $usuario["id"], PDO::PARAM_INT);
        $stmtHash->execute();
    }

    session_regenerate_id(true);

    $_SESSION["usuario_id"] = $usuario["id"];
    $_SESSION["usuario"] = $usuario["username"];
    $_SESSION["rol"] = $usuario["rol_nombre"];

    registrarAuditoria($conexion, $usuario["id"], $usuario["username"], "LOGIN_EXITOSO", "Ingreso al sistema");

    if($usuario["rol_nombre"] == "Administrador"){
        header("Location: ../frontend/admin.php");
    } elseif($usuario["rol_nombre"] == "Docente"){
        header("Location: ../frontend/docente.php");
    } else {
        header("Location: ../frontend/estudiante.php");
    }
    exit();
}
