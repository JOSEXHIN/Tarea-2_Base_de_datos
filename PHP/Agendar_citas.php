<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 1) { header("Location: index.php"); exit(); }

$id_paciente = $_SESSION['usuario_id'];
$mensaje = '';

$especialidades = $pdo->query("SELECT * FROM Especialidad")->fetchAll(PDO::FETCH_ASSOC);
$centros = $pdo->query("SELECT * FROM Centro_Medico")->fetchAll(PDO::FETCH_ASSOC);
$medicos = $pdo->query("SELECT * FROM Medico")->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fecha_hora = $_POST['fecha'] . ' ' . $_POST['hora'] . ':00';
    try {
        $stmt = $pdo->prepare("CALL SP_Agendar_Cita(?, ?, ?, ?, ?)");
        $stmt->execute([$fecha_hora, $id_paciente, $_POST['id_medico'], $_POST['id_centro'], $_POST['id_especialidad']]);
        $mensaje = "<div class='alert alert-success'>Cita agendada exitosamente. <a href='paciente_dashboard.php'>Volver al panel</a>.</div>";
    } catch (PDOException $e) {
        $mensaje = "<div class='alert alert-danger'>No se pudo agendar: " . $e->getMessage() . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"></head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card shadow">
        <div class="card-header bg-success text-white"><h4 class="mb-0">Agendar Nueva Cita</h4></div>
        <div class="card-body">
            <?= $mensaje ?>
            <form method="POST">
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label>Especialidad</label>
                        <select name="id_especialidad" class="form-select" required>
                            <?php foreach($especialidades as $esp): ?><option value="<?= $esp['id_especialidad'] ?>"><?= $esp['nombre'] ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label>Centro Médico</label>
                        <select name="id_centro" class="form-select" required>
                            <?php foreach($centros as $cen): ?><option value="<?= $cen['id_centro'] ?>"><?= $cen['nombre'] ?></option><?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label>Médico</label>
                        <select name="id_medico" class="form-select" required>
                            <?php foreach($medicos as $med): ?><option value="<?= $med['id_medico'] ?>"><?= $med['nombre_completo'] ?></option><?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Fecha</label><input type="date" name="fecha" class="form-control" required min="<?= date('Y-m-d') ?>">
                    </div>
                    <div class="col-md-4 mb-3">
                        <label>Hora</label><input type="time" name="hora" class="form-control" required step="1800" min="08:00" max="18:00">
                    </div>
                </div>
                <button type="submit" class="btn btn-success">Agendar</button>
                <a href="paciente_dashboard.php" class="btn btn-secondary">Cancelar</a>
            </form>
        </div>
    </div>
</div>
</body>
</html>
