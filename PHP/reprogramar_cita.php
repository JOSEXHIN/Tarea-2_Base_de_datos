<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 1 || !isset($_GET['id'])) { header("Location: index.php"); exit(); }

$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fecha_hora = $_POST['fecha'] . ' ' . $_POST['hora'] . ':00';
    try {
        $stmt = $pdo->prepare("UPDATE Cita SET fecha_hora = ? WHERE id_cita = ? AND id_paciente = ? AND fecha_hora > NOW()");
        $stmt->execute([$fecha_hora, $_GET['id'], $_SESSION['usuario_id']]);
        header("Location: paciente_dashboard.php"); exit();
    } catch (PDOException $e) { $mensaje = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>"; }
}
?>
<!DOCTYPE html>
<html lang="es">
<head><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-warning"><h4 class="mb-0">Reprogramar Cita</h4></div>
        <div class="card-body">
            <?= $mensaje ?>
            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3"><label>Nueva Fecha</label><input type="date" name="fecha" class="form-control" required min="<?= date('Y-m-d') ?>"></div>
                    <div class="col-md-6 mb-3"><label>Nueva Hora</label><input type="time" name="hora" class="form-control" required step="1800" min="08:00" max="18:00"></div>
                </div>
                <button type="submit" class="btn btn-warning">Guardar Cambios</button>
                <a href="paciente_dashboard.php" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
