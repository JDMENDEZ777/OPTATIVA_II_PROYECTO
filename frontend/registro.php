<?php
session_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro</title>
    <link rel="stylesheet" href="login.css">
    <link rel="shortcut icon" href="../img/logofet.png" type="image/x-icon">
</head>
<body>

<div class="container">
    <div class="form-container">

        <div class="logo">
            <img src="../img/logofet.png" alt="Logo FET">
        </div>

        <form action="../backend/registro.php" method="POST">

            <?php if(isset($_SESSION['mensaje'])): ?>
                <div class="message error">
                    <?php
                        echo htmlspecialchars($_SESSION['mensaje']);
                        unset($_SESSION['mensaje']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="form-group">
                <label>Usuario</label>
                <input type="text" name="username"
                    maxlength="30"
                    pattern="[A-Za-z]{1,30}"
                    title="Solo letras, maximo 30 caracteres, sin espacios"
                    required>
            </div>

            <div class="form-group">
                <label>Correo</label>
                <input type="email" name="email" required>
            </div>

            <div class="form-group">
                <label>Rol</label>
                <select name="rol" required>
                    <option value="">Seleccione un rol</option>
                    <option value="Estudiante">Estudiante</option>
                    <option value="Docente">Docente</option>
                </select>
            </div>

            <div class="form-group">
                <label>Contrasena</label>
                <input type="password" name="password"
                    maxlength="30"
                    pattern="[A-Za-z0-9]{6,30}"
                    title="Solo letras y numeros, entre 6 y 30 caracteres, sin espacios"
                    required>
            </div>

            <div class="form-group">
                <label>Confirmar contrasena</label>
                <input type="password" name="confirmar"
                    maxlength="30"
                    pattern="[A-Za-z0-9]{6,30}"
                    title="Solo letras y numeros, entre 6 y 30 caracteres, sin espacios"
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
