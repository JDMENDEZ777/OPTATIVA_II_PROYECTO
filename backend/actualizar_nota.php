<?php
session_start();
require_once("../database/conexion.php");
require_once("auditoria.php");
require_once("sesion.php");

function volverAlModulo(){
    redirigirModuloActual();
}

validarRolesPermitidos(["Administrador", "Docente"]);

if($_SERVER["REQUEST_METHOD"] != "POST"){
    volverAlModulo();
}

validarContextoFormulario();

$notaId = $_POST["nota_id"] ?? "";
$notaNueva = $_POST["nota"] ?? "";
$observacionNueva = trim($_POST["observacion"] ?? "");
$estadoNuevo = $_POST["estado"] ?? null;
$usuarioId = $_SESSION["usuario_id"];
$username = $_SESSION["usuario"];
$rol = $_SESSION["rol"];

if(!ctype_digit($notaId) || !is_numeric($notaNueva) || $notaNueva < 0 || $notaNueva > 5){
    $_SESSION["mensaje"] = "Datos de nota invalidos.";
    volverAlModulo();
}

$sql = "SELECT id, docente_id, nota, estado, observacion
        FROM notas
        WHERE id = :id";
$stmt = $conexion->prepare($sql);
$stmt->bindParam(":id", $notaId, PDO::PARAM_INT);
$stmt->execute();
$notaActual = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$notaActual){
    $_SESSION["mensaje"] = "La nota no existe.";
    volverAlModulo();
}

if($rol == "Docente"){
    if((int)$notaActual["docente_id"] !== (int)$usuarioId || $notaActual["estado"] != "activa"){
        registrarAuditoria($conexion, $usuarioId, $username, "EDICION_NOTA_DENEGADA", "Intento de modificar nota no activa o ajena");
        $_SESSION["mensaje"] = "Solo puede editar notas propias en estado activa.";
        volverAlModulo();
    }
    $estadoNuevo = $notaActual["estado"];
}

if($rol == "Administrador"){
    if(!in_array($estadoNuevo, ["activa", "bloqueada", "inactiva"])){
        $_SESSION["mensaje"] = "Estado de nota invalido.";
        volverAlModulo();
    }
}

$sqlUpdate = "UPDATE notas
              SET nota = :nota,
                  observacion = :observacion,
                  estado = :estado,
                  actualizado_en = CURRENT_TIMESTAMP
              WHERE id = :id";
$stmtUpdate = $conexion->prepare($sqlUpdate);
$stmtUpdate->bindParam(":nota", $notaNueva);
$stmtUpdate->bindParam(":observacion", $observacionNueva);
$stmtUpdate->bindParam(":estado", $estadoNuevo);
$stmtUpdate->bindParam(":id", $notaId, PDO::PARAM_INT);
$stmtUpdate->execute();

$sqlLog = "INSERT INTO logs_notas
           (nota_id, usuario_id, username, accion, nota_anterior, nota_nueva, estado_anterior, estado_nuevo, detalle)
           VALUES
           (:nota_id, :usuario_id, :username, 'MODIFICACION_NOTA', :nota_anterior, :nota_nueva, :estado_anterior, :estado_nuevo, :detalle)";
$detalle = "Modificacion realizada por rol " . $rol;
$stmtLog = $conexion->prepare($sqlLog);
$stmtLog->bindParam(":nota_id", $notaId, PDO::PARAM_INT);
$stmtLog->bindParam(":usuario_id", $usuarioId, PDO::PARAM_INT);
$stmtLog->bindParam(":username", $username);
$stmtLog->bindParam(":nota_anterior", $notaActual["nota"]);
$stmtLog->bindParam(":nota_nueva", $notaNueva);
$stmtLog->bindParam(":estado_anterior", $notaActual["estado"]);
$stmtLog->bindParam(":estado_nuevo", $estadoNuevo);
$stmtLog->bindParam(":detalle", $detalle);
$stmtLog->execute();

registrarAuditoria($conexion, $usuarioId, $username, "MODIFICACION_NOTA", "Nota " . $notaId . " modificada");

$_SESSION["mensaje"] = "Nota actualizada correctamente.";
volverAlModulo();
