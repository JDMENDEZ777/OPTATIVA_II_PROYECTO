<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - Plataforma Académica</title>
    <link rel="stylesheet" href="../frontend/login.css">
    <link rel="shortcut icon" href="../img/logofet.png" type="image/x-icon">
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