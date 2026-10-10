<?php
session_start();
require 'conexion.php';

// Validar que el usuario sea un Médico
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 2) { 
    header("Location: index.php"); 
    exit(); 
}

$id_medico = $_SESSION['usuario_id'];

// Consulta para obtener la agenda diaria del médico
$stmt = $pdo->prepare("
    SELECT c.id_cita, c.fecha_hora, p.nombre_completo AS paciente, p.id_prevision, cm.nombre AS centro, ec.nombre AS estado
    FROM Cita c
    JOIN Paciente p ON c.id_paciente = p.id_paciente
    JOIN Centro_Medico cm ON c.id_centro = cm.id_centro
    JOIN Estado_Cita ec ON c.id_estado = ec.id_estado
    WHERE c.id_medico = ? AND DATE(c.fecha_hora) = CURDATE()
    ORDER BY c.fecha_hora ASC
");
$stmt->execute([$id_medico]);
$agenda_hoy = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Médico</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">SaludUSM - Médico</a>
            <div class="d-flex">
                <span class="navbar-text text-white me-3">Dr(a). <?= htmlspecialchars($_SESSION['nombre']) ?></span>
                <a href="Cerrar_sesion.php" class="btn btn-outline-light btn-sm">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-info text-white"><h5 class="mb-0">Mi Agenda Diaria</h5></div>
            <div class="card-body">
                <?php if(count($agenda_hoy) > 0): ?>
                    <table class="table table-hover">
                        <thead>
                            <tr><th>Hora</th><th>Paciente</th><th>Previsión</th><th>Centro</th><th>Estado</th><th>Acciones</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($agenda_hoy as $cita): ?>
                                <tr>
                                    <td><?= date('H:i', strtotime($cita['fecha_hora'])) ?></td>
                                    <td><?= htmlspecialchars($cita['paciente']) ?></td>
                                    <td><?= htmlspecialchars($cita['id_prevision']) ?></td>
                                    <td><?= htmlspecialchars($cita['centro']) ?></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($cita['estado']) ?></span></td>
                                    <td>
                                        <!-- Aquí irá tu enlace para registrar la atención -->
                                        <a href="registrar_atencion.php?id_cita=<?= $cita['id_cita'] ?>" class="btn btn-success btn-sm">Atender</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted">No tienes citas agendadas para hoy.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
