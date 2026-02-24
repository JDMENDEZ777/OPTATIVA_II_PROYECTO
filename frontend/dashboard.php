<?php
session_start();

if(!isset($_SESSION["usuario"])){
    header("Location: procesar.php");
    exit();
}

$usuario = $_SESSION["usuario"];
$rol = $_SESSION["rol"];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>

<div class="dashboard-container">

    <div class="dashboard-card">

        <div class="welcome-section">
            <h1>
                <?php
                    if($rol == "Administrador"){
                        echo "Bienvenido Administrador: " . $usuario;
                    } else {
                        echo "Bienvenido Estudiante: " . $usuario;
                    }
                ?>
            </h1>

            <a href="../backend/logout.php" class="logout-btn">
                Cerrar sesión
            </a>
        </div>

        <div class="dashboard-logo">
            <img src="../img/logofet.png" alt="Logo FET">
        </div>

    </div>

</div>

</body>
</html>