<?php
session_start();

// Si ya está logueado, redirigir
if(isset($_SESSION["usuario"])){
    header("Location: dashboard.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Plataforma Académica</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>

<div class="container">
    <div class="form-container">

        <div class="logo">
            <img src="../img/logofet.png" alt="FET Logo">
        </div>

        <form action="../backend/login.php" method="POST">

            <?php if(isset($_SESSION['mensaje'])): ?>
                <div class="message error">
                    <?php 
                        echo $_SESSION['mensaje']; 
                        unset($_SESSION['mensaje']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label for="username">Usuario</label>
                <input type="text" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required>
            </div>
            
            <button type="submit" class="register-btn">
                Iniciar Sesión
            </button>

        </form>
    </div>
    
    <div class="promo-image">
        <img src="../img/image.png" alt="Somos Generación FET">
    </div>
</div>

</body>
</html>

<?php
session_start();
require_once("../database/conexion.php");

if($_SERVER["REQUEST_METHOD"] == "POST"){

    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    // Validaciones obligatorias del taller

    if(empty($username)){
        $_SESSION["mensaje"] = "El usuario está vacío.";
        header("Location: ../frontend/login.php");
        exit();
    }

    if(empty($password)){
        $_SESSION["mensaje"] = "La contraseña está vacía.";
        header("Location: ../frontend/login.php");
        exit();
    }

    // Buscar usuario con rol
    $sql = "SELECT u.*, r.nombre AS rol_nombre
            FROM usuarios u
            INNER JOIN roles r ON u.rol_id = r.id
            WHERE u.username = :username";

    $stmt = $conexion->prepare($sql);
    $stmt->bindParam(":username", $username);
    $stmt->execute();

    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$usuario){
        $_SESSION["mensaje"] = "El usuario no existe.";
        header("Location: ../frontend/login.php");
        exit();
    }

    if($usuario["password"] !== $password){
        $_SESSION["mensaje"] = "Contraseña incorrecta.";
        header("Location: ../frontend/login.php");
        exit();
    }

    // Login correcto
    $_SESSION["usuario"] = $usuario["username"];
    $_SESSION["rol"] = $usuario["rol_nombre"];

    header("Location: ../frontend/dashboard.php");
    exit();
}