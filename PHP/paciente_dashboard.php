<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 1) {
    header("Location: index.php");
    exit();
}

$id_paciente = $_SESSION['usuario_id'];

// CITAS PROXIMAS
$stmt_proximas = $pdo->prepare("
    SELECT c.id_cita, c.fecha_hora, m.nombre_completo AS medico, e.nombre AS especialidad, cm.nombre AS centro, ec.nombre AS estado 
    FROM Cita c
    JOIN Medico m ON c.id_medico = m.id_medico
    JOIN Especialidad e ON c.id_especialidad = e.id_especialidad
    JOIN Centro_Medico cm ON c.id_centro = cm.id_centro
    JOIN Estado_Cita ec ON c.id_estado = ec.id_estado
    WHERE c.id_paciente = ? AND c.fecha_hora >= NOW() AND ec.nombre IN ('Reservada', 'Confirmada')
    ORDER BY c.fecha_hora ASC
");
$stmt_proximas->execute([$id_paciente]);
$proximas_citas = $stmt_proximas->fetchAll(PDO::FETCH_ASSOC);

// HISTORIAL
$stmt_historial = $pdo->prepare("
    SELECT c.id_cita, c.fecha_hora, m.nombre_completo AS medico, e.nombre AS especialidad, cm.nombre AS centro, ec.nombre AS estado 
    FROM Cita c
    JOIN Medico m ON c.id_medico = m.id_medico
    JOIN Especialidad e ON c.id_especialidad = e.id_especialidad
    JOIN Centro_Medico cm ON c.id_centro = cm.id_centro
    JOIN Estado_Cita ec ON c.id_estado = ec.id_estado
    WHERE c.id_paciente = ? AND (c.fecha_hora < NOW() OR ec.nombre NOT IN ('Reservada', 'Confirmada'))
    ORDER BY c.fecha_hora DESC
");
$stmt_historial->execute([$id_paciente]);
$historial_citas = $stmt_historial->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Paciente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">SaludUSM - Paciente</a>
            <div class="d-flex">
                <span class="navbar-text text-white me-3">Hola, <?= htmlspecialchars($_SESSION['nombre']) ?></span>
                <a href="logout.php" class="btn btn-outline-light btn-sm">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Mis Citas</h2>
            <a href="agendar_cita.php" class="btn btn-success">Agendar Nueva Hora</a>
        </div>

        <div class="card shadow-sm mb-4">
            <div class="card-header bg-info text-white"><h5 class="mb-0">Próximas Citas</h5></div>
            <div class="card-body">
                <?php if(count($proximas_citas) > 0): ?>
                    <table class="table table-hover">
                        <thead>
                            <tr><th>Fecha y Hora</th><th>Médico</th><th>Especialidad</th><th>Centro</th><th>Estado</th><th>Acciones</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($proximas_citas as $cita): ?>
                                <tr>
                                    <td><?= date('d-m-Y H:i', strtotime($cita['fecha_hora'])) ?></td>
                                    <td><?= $cita['medico'] ?></td>
                                    <td><?= $cita['especialidad'] ?></td>
                                    <td><?= $cita['centro'] ?></td>
                                    <td><span class="badge bg-primary"><?= $cita['estado'] ?></span></td>
                                    <td>
                                        <a href="reprogramar_cita.php?id=<?= $cita['id_cita'] ?>" class="btn btn-warning btn-sm">Reprogramar</a>
                                        <a href="cancelar_cita.php?id=<?= $cita['id_cita'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Está seguro de cancelar esta cita?');">Cancelar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted">No tienes citas próximas agendadas.</p>
                <?php endif; ?>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-secondary text-white"><h5 class="mb-0">Historial de Atenciones</h5></div>
            <div class="card-body">
                <?php if(count($historial_citas) > 0): ?>
                    <table class="table table-hover">
                        <thead>
                            <tr><th>Fecha y Hora</th><th>Médico</th><th>Especialidad</th><th>Centro</th><th>Estado</th></tr>
                        </thead>
                        <tbody>
                            <?php foreach($historial_citas as $cita): ?>
                                <tr>
                                    <td><?= date('d-m-Y H:i', strtotime($cita['fecha_hora'])) ?></td>
                                    <td><?= $cita['medico'] ?></td>
                                    <td><?= $cita['especialidad'] ?></td>
                                    <td><?= $cita['centro'] ?></td>
                                    <td><?= $cita['estado'] ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p class="text-muted">No tienes historial de atenciones previas.</p>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="mt-5 text-end">
             <a href="eliminar_cuenta.php" class="btn btn-outline-danger" onclick="return confirm('PELIGRO: ¿Está seguro de eliminar su cuenta? Esto cancelará todas sus citas futuras.');">Eliminar Mi Cuenta</a>
        </div>
    </div>
</body>
</html>
