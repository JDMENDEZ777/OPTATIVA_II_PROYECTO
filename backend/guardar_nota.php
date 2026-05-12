<?php
session_start();
require_once("../database/conexion.php");
require_once("auditoria.php");
require_once("sesion.php");

function volverAlModulo(){
    redirigirModuloActual();
}

validarRolesPermitidos(["Administrador", "Docente"]);

if($_SERVER["REQUEST_METHOD"] == "POST"){
    validarContextoFormulario();

    $estudianteId = $_POST["estudiante_id"] ?? "";
    $materiaId = $_POST["materia_id"] ?? "";
    $nota = $_POST["nota"] ?? "";
    $observacion = trim($_POST["observacion"] ?? "");
    $docenteId = $_SESSION["usuario_id"];
    $usernameSesion = $_SESSION["usuario"];

    if(!ctype_digit($estudianteId) || !ctype_digit($materiaId) || !is_numeric($nota) || $nota < 0 || $nota > 5){
        $_SESSION["mensaje"] = "Datos de nota invalidos.";
        volverAlModulo();
    }

    $sql = "INSERT INTO notas (estudiante_id, docente_id, materia_id, nota, observacion, estado)
            VALUES (:estudiante_id, :docente_id, :materia_id, :nota, :observacion, 'activa')
            RETURNING id";

    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(":estudiante_id", $estudianteId);
    $stmt->bindParam(":docente_id", $docenteId);
    $stmt->bindParam(":materia_id", $materiaId);
    $stmt->bindParam(":nota", $nota);
    $stmt->bindParam(":observacion", $observacion);
    $stmt->execute();
    $notaId = $stmt->fetchColumn();

    registrarAuditoria($conexion, $docenteId, $usernameSesion, "REGISTRO_NOTA", "Nota registrada");

    $sqlLog = "INSERT INTO logs_notas
               (nota_id, usuario_id, username, accion, nota_nueva, estado_nuevo, detalle)
               VALUES (:nota_id, :usuario_id, :username, 'CREACION_NOTA', :nota_nueva, 'activa', 'Nota creada')";
    $stmtLog = $conexion->prepare($sqlLog);
    $stmtLog->bindParam(":nota_id", $notaId, PDO::PARAM_INT);
    $stmtLog->bindParam(":usuario_id", $docenteId, PDO::PARAM_INT);
    $stmtLog->bindParam(":username", $usernameSesion);
    $stmtLog->bindParam(":nota_nueva", $nota);
    $stmtLog->execute();

    $_SESSION["mensaje"] = "Nota registrada correctamente.";
}

volverAlModulo();
