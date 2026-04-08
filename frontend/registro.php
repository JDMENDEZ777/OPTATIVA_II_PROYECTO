<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>
    <link rel="stylesheet" href="login.css">
</head>
<body>

<div class="container">
    <div class="form-container">

        <div class="logo">
            <img src="../img/logofet.png">
        </div>

        <form action="../backend/registro.php" method="POST">

            <?php if(isset($_SESSION['mensaje'])): ?>
                <div class="message error">
                    <?php 
                        echo $_SESSION['mensaje']; 
                        unset($_SESSION['mensaje']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label>Usuario</label>
                <input type="text" name="username"
                    maxlength="30"
                    pattern="[A-Za-z]{1,30}"
                    required>
            </div>

            <div class="form-group">
                <label>Correo</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group">
                <label>Contraseña</label>
                <input type="password" name="password"
                    maxlength="30"
                    pattern="[A-Za-z0-9]{6,30}"
                    required>
            </div>

            <div class="form-group">
                <label>Confirmar Contraseña</label>
                <input type="password" name="confirmar"
                    maxlength="30"
                    pattern="[A-Za-z0-9]{6,30}"
                    required>
            </div>

            <button class="register-btn">Registrarse</button>

            <div class="forgot-password">
                <a href="procesar.php">Volver al login</a>
            </div>

        </form>

    </div>
</div>

</body>
</html>