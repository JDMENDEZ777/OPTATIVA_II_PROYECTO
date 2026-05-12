<?php
session_start();

if(!isset($_SESSION["usuario"])){
    header("Location: procesar.php");
    exit();
}

if($_SESSION["rol"] == "Administrador"){
    header("Location: admin.php");
} elseif($_SESSION["rol"] == "Docente"){
    header("Location: docente.php");
} else {
    header("Location: estudiante.php");
}

exit();
