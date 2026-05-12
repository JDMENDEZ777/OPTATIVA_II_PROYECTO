<?php
function registrarAuditoria($conexion, $usuarioId, $username, $accion, $detalle = ""){
    $ip = $_SERVER["REMOTE_ADDR"] ?? "desconocida";

    $sql = "INSERT INTO auditoria (usuario_id, username, accion, detalle, ip)
            VALUES (:usuario_id, :username, :accion, :detalle, :ip)";

    $stmt = $conexion->prepare($sql);
    $stmt->bindValue(":usuario_id", $usuarioId, $usuarioId === null ? PDO::PARAM_NULL : PDO::PARAM_INT);
    $stmt->bindParam(":username", $username);
    $stmt->bindParam(":accion", $accion);
    $stmt->bindParam(":detalle", $detalle);
    $stmt->bindParam(":ip", $ip);
    $stmt->execute();
}

