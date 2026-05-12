<?php
session_start();
require_once("../database/conexion.php");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if(!isset($_SESSION["usuario"]) || $_SESSION["rol"] != "Docente"){
    header("Location: procesar.php");
    exit();
}

$usuario = $_SESSION["usuario"];
$usuarioId = $_SESSION["usuario_id"];

$estudiantes = $conexion->query("SELECT id, username FROM usuarios WHERE rol_id = 2 AND bloqueado = false ORDER BY username")->fetchAll(PDO::FETCH_ASSOC);
$materias = $conexion->query("SELECT id, nombre FROM materias ORDER BY nombre")->fetchAll(PDO::FETCH_ASSOC);

$stmtNotas = $conexion->prepare("SELECT n.id, e.username AS estudiante, m.nombre AS materia, n.nota, n.estado, n.observacion
                                 FROM notas n
                                 INNER JOIN usuarios e ON n.estudiante_id = e.id
                                 INNER JOIN materias m ON n.materia_id = m.id
                                 WHERE n.docente_id = :docente_id
                                 ORDER BY n.creado_en DESC");
$stmtNotas->bindParam(":docente_id", $usuarioId, PDO::PARAM_INT);
$stmtNotas->execute();
$notas = $stmtNotas->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Modulo Docente</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="shortcut icon" href="../img/logofet.png" type="image/x-icon">
</head>
<body>
<div class="dashboard-container">
    <div class="dashboard-card">
        <div class="welcome-section">
            <h1>Modulo Docente: <?php echo htmlspecialchars($usuario); ?></h1>
            <?php if(isset($_SESSION["mensaje"])): ?>
                <div class="dashboard-message"><?php echo htmlspecialchars($_SESSION["mensaje"]); unset($_SESSION["mensaje"]); ?></div>
            <?php endif; ?>
            <a href="../backend/logout.php" class="logout-btn">Cerrar sesion</a>
        </div>
        <div class="dashboard-logo"><img src="../img/logofet.png" alt="Logo FET"></div>
    </div>

    <div class="panel-grid">
        <section class="panel">
            <h2>Registrar nota</h2>
            <form action="../backend/guardar_nota.php" method="POST">
                <input type="hidden" name="form_rol" value="<?php echo htmlspecialchars($_SESSION["rol"]); ?>">
                <input type="hidden" name="form_usuario_id" value="<?php echo htmlspecialchars($_SESSION["usuario_id"]); ?>">
                <select name="estudiante_id" required>
                    <option value="">Estudiante</option>
                    <?php foreach($estudiantes as $estudiante): ?>
                        <option value="<?php echo $estudiante["id"]; ?>"><?php echo htmlspecialchars($estudiante["username"]); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="materia_id" required>
                    <option value="">Curso</option>
                    <?php foreach($materias as $materia): ?>
                        <option value="<?php echo $materia["id"]; ?>"><?php echo htmlspecialchars($materia["nombre"]); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="nota" min="0" max="5" step="0.01" placeholder="Nota 0.00 - 5.00" required>
                <input type="text" name="observacion" maxlength="255" placeholder="Observacion">
                <button type="submit">Guardar nota</button>
            </form>
        </section>

        <section class="panel">
            <h2>Registrar inasistencia</h2>
            <form action="../backend/guardar_inasistencia.php" method="POST">
                <input type="hidden" name="form_rol" value="<?php echo htmlspecialchars($_SESSION["rol"]); ?>">
                <input type="hidden" name="form_usuario_id" value="<?php echo htmlspecialchars($_SESSION["usuario_id"]); ?>">
                <select name="estudiante_id" required>
                    <option value="">Estudiante</option>
                    <?php foreach($estudiantes as $estudiante): ?>
                        <option value="<?php echo $estudiante["id"]; ?>"><?php echo htmlspecialchars($estudiante["username"]); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="materia_id" required>
                    <option value="">Curso</option>
                    <?php foreach($materias as $materia): ?>
                        <option value="<?php echo $materia["id"]; ?>"><?php echo htmlspecialchars($materia["nombre"]); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="date" name="fecha" required>
                <label class="check-line"><input type="checkbox" name="justificada"> Justificada</label>
                <input type="text" name="observacion" maxlength="255" placeholder="Observacion">
                <button type="submit">Guardar inasistencia</button>
            </form>
        </section>
    </div>

    <section class="panel audit-panel">
        <h2>Mis notas registradas</h2>
        <table>
            <thead>
                <tr><th>Estudiante</th><th>Curso</th><th>Nota</th><th>Estado</th><th>Observacion</th><th>Accion</th></tr>
            </thead>
            <tbody>
                <?php foreach($notas as $nota): ?>
                    <tr>
                        <?php if($nota["estado"] == "activa"): ?>
                            <form action="../backend/actualizar_nota.php" method="POST">
                                <input type="hidden" name="form_rol" value="<?php echo htmlspecialchars($_SESSION["rol"]); ?>">
                                <input type="hidden" name="form_usuario_id" value="<?php echo htmlspecialchars($_SESSION["usuario_id"]); ?>">
                                <input type="hidden" name="nota_id" value="<?php echo $nota["id"]; ?>">
                                <td><?php echo htmlspecialchars($nota["estudiante"]); ?></td>
                                <td><?php echo htmlspecialchars($nota["materia"]); ?></td>
                                <td><input class="table-input" type="number" name="nota" min="0" max="5" step="0.01" value="<?php echo htmlspecialchars($nota["nota"]); ?>" required></td>
                                <td><?php echo htmlspecialchars($nota["estado"]); ?></td>
                                <td><input class="table-input" type="text" name="observacion" maxlength="255" value="<?php echo htmlspecialchars($nota["observacion"]); ?>"></td>
                                <td><button type="submit">Actualizar</button></td>
                            </form>
                        <?php else: ?>
                            <td><?php echo htmlspecialchars($nota["estudiante"]); ?></td>
                            <td><?php echo htmlspecialchars($nota["materia"]); ?></td>
                            <td><?php echo htmlspecialchars($nota["nota"]); ?></td>
                            <td><?php echo htmlspecialchars($nota["estado"]); ?></td>
                            <td><?php echo htmlspecialchars($nota["observacion"]); ?></td>
                            <td>Sin permiso</td>
                        <?php endif; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </section>
</div>
<script>
window.addEventListener("pageshow", function(event){
    if(event.persisted){
        window.location.reload();
    }
});
</script>
</body>
</html>
