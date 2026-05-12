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
    $fecha = $_POST["fecha"] ?? "";
    $justificada = isset($_POST["justificada"]) ? "true" : "false";
    $observacion = trim($_POST["observacion"] ?? "");
    $docenteId = $_SESSION["usuario_id"];

    if(!ctype_digit($estudianteId) || !ctype_digit($materiaId) || !preg_match("/^\d{4}-\d{2}-\d{2}$/", $fecha)){
        $_SESSION["mensaje"] = "Datos de inasistencia invalidos.";
        volverAlModulo();
    }

    $sql = "INSERT INTO inasistencias (estudiante_id, docente_id, materia_id, fecha, justificada, observacion)
            VALUES (:estudiante_id, :docente_id, :materia_id, :fecha, :justificada, :observacion)";

    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(":estudiante_id", $estudianteId);
    $stmt->bindParam(":docente_id", $docenteId);
    $stmt->bindParam(":materia_id", $materiaId);
    $stmt->bindParam(":fecha", $fecha);
    $stmt->bindParam(":justificada", $justificada);
    $stmt->bindParam(":observacion", $observacion);
    $stmt->execute();

    registrarAuditoria($conexion, $docenteId, $_SESSION["usuario"], "REGISTRO_INASISTENCIA", "Inasistencia registrada");
    $_SESSION["mensaje"] = "Inasistencia registrada correctamente.";
}

volverAlModulo();
