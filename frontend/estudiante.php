<?php
session_start();
require_once("../database/conexion.php");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

if(!isset($_SESSION["usuario"]) || $_SESSION["rol"] != "Estudiante"){
    header("Location: procesar.php");
    exit();
}

$usuario = $_SESSION["usuario"];
$usuarioId = $_SESSION["usuario_id"];

$stmtNotas = $conexion->prepare("SELECT m.nombre AS materia, n.nota, n.estado, n.observacion, n.creado_en
                                 FROM notas n
                                 INNER JOIN materias m ON n.materia_id = m.id
                                 WHERE n.estudiante_id = :id AND n.estado <> 'inactiva'
                                 ORDER BY n.creado_en DESC");
$stmtNotas->bindParam(":id", $usuarioId, PDO::PARAM_INT);
$stmtNotas->execute();
$notas = $stmtNotas->fetchAll(PDO::FETCH_ASSOC);

$stmtInasistencias = $conexion->prepare("SELECT m.nombre AS materia, i.fecha, i.justificada, i.observacion
                                         FROM inasistencias i
                                         INNER JOIN materias m ON i.materia_id = m.id
                                         WHERE i.estudiante_id = :id
                                         ORDER BY i.fecha DESC");
$stmtInasistencias->bindParam(":id", $usuarioId, PDO::PARAM_INT);
$stmtInasistencias->execute();
$inasistencias = $stmtInasistencias->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Modulo Estudiante</title>
    <link rel="stylesheet" href="dashboard.css">
    <link rel="shortcut icon" href="../img/logofet.png" type="image/x-icon">
</head>
<body>
<div class="dashboard-container">
    <div class="dashboard-card">
        <div class="welcome-section">
            <h1>Modulo Estudiante: <?php echo htmlspecialchars($usuario); ?></h1>
            <?php if(isset($_SESSION["mensaje"])): ?>
                <div class="dashboard-message"><?php echo htmlspecialchars($_SESSION["mensaje"]); unset($_SESSION["mensaje"]); ?></div>
            <?php endif; ?>
            <a href="../backend/logout.php" class="logout-btn">Cerrar sesion</a>
        </div>
        <div class="dashboard-logo"><img src="../img/logofet.png" alt="Logo FET"></div>
    </div>

    <div class="panel-grid">
        <section class="panel">
            <h2>Mis notas</h2>
            <table>
                <thead><tr><th>Curso</th><th>Nota</th><th>Estado</th><th>Observacion</th></tr></thead>
                <tbody>
                    <?php foreach($notas as $nota): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($nota["materia"]); ?></td>
                            <td><?php echo htmlspecialchars($nota["nota"]); ?></td>
                            <td><?php echo htmlspecialchars($nota["estado"]); ?></td>
                            <td><?php echo htmlspecialchars($nota["observacion"]); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>

        <section class="panel">
            <h2>Mis inasistencias</h2>
            <table>
                <thead><tr><th>Curso</th><th>Fecha</th><th>Estado</th><th>Observacion</th></tr></thead>
                <tbody>
                    <?php foreach($inasistencias as $inasistencia): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($inasistencia["materia"]); ?></td>
                            <td><?php echo htmlspecialchars($inasistencia["fecha"]); ?></td>
                            <td><?php echo $inasistencia["justificada"] ? "Justificada" : "No justificada"; ?></td>
                            <td><?php echo htmlspecialchars($inasistencia["observacion"]); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </div>
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
