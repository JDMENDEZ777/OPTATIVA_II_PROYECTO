<?php

function rutaModuloActual(){
    if(!isset($_SESSION["rol"])){
        return "../frontend/procesar.php";
    }

    if($_SESSION["rol"] == "Administrador"){
        return "../frontend/admin.php";
    }

    if($_SESSION["rol"] == "Docente"){
        return "../frontend/docente.php";
    }

    if($_SESSION["rol"] == "Estudiante"){
        return "../frontend/estudiante.php";
    }

    return "../frontend/procesar.php";
}

function redirigirModuloActual(){
    header("Location: " . rutaModuloActual());
    exit();
}

function validarSesionActiva(){
    if(!isset($_SESSION["usuario"], $_SESSION["usuario_id"], $_SESSION["rol"])){
        header("Location: ../frontend/procesar.php");
        exit();
    }
}

function validarRolesPermitidos($rolesPermitidos){
    validarSesionActiva();

    if(!in_array($_SESSION["rol"], $rolesPermitidos)){
        $_SESSION["mensaje"] = "La sesion actual no tiene permisos para realizar esta accion.";
        redirigirModuloActual();
    }
}

function validarContextoFormulario(){
    validarSesionActiva();

    $formRol = $_POST["form_rol"] ?? "";
    $formUsuarioId = $_POST["form_usuario_id"] ?? "";

    if($formRol === "" || $formUsuarioId === ""){
        $_SESSION["mensaje"] = "Formulario invalido. Actualice la pagina e intente nuevamente.";
        redirigirModuloActual();
    }

    if($formRol !== $_SESSION["rol"] || (int)$formUsuarioId !== (int)$_SESSION["usuario_id"]){
        $_SESSION["mensaje"] = "La sesion cambio en otra pestana. No se guardo ningun cambio.";
        redirigirModuloActual();
    }
}

