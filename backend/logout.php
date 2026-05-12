<?php
session_start();
require_once("../database/conexion.php");
require_once("auditoria.php");

if(isset($_SESSION["usuario_id"])){
    registrarAuditoria($conexion, $_SESSION["usuario_id"], $_SESSION["usuario"], "LOGOUT", "Salida del sistema");
}

session_destroy();
header("Location: ../frontend/procesar.php");
exit();
