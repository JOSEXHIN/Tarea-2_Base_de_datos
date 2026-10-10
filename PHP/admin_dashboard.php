<?php
session_start();
require 'conexion.php';


if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 3) { 
    header("Location: index.php"); 
    exit(); 
}


$stmt_centros = $pdo->query("
    SELECT 
        cm.nombre AS centro, 
        COUNT(c.id_cita) AS total_citas,
        SUM(CASE WHEN c.id_estado = 4 THEN 1 ELSE 0 END) AS total_inasistencias,
        ROUND((SUM(CASE WHEN c.id_estado = 4 THEN 1 ELSE 0 END) / NULLIF(COUNT(c.id_cita), 0)) * 100, 2) AS porcentaje_inasistencia
    FROM Centro_Medico cm
    LEFT JOIN Cita c ON cm.id_centro = c.id_centro
    GROUP BY cm.id_centro, cm.nombre
");
$estadisticas_centros = $stmt_centros->fetchAll(PDO::FETCH_ASSOC);


$stmt_diag = $pdo->query("
    SELECT codigo_cie10, descripcion, COUNT(*) AS frecuencia 
    FROM Diagnostico 
    GROUP BY codigo_cie10, descripcion 
    ORDER BY frecuencia DESC 
    LIMIT 5
");
$top_diagnosticos = $stmt_diag->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
        <div class="container">
            <a class="navbar-brand" href="#">SaludUSM - Admin</a>
            <div class="d-flex">
                <span class="navbar-text text-white me-3"><?= htmlspecialchars($_SESSION['nombre']) ?></span>
                <a href="Cerrar_sesion.php" class="btn btn-outline-light btn-sm">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="mb-4 d-flex gap-2">
            <!-- Botones para los CRUDs que faltan -->
            <a href="crud_centros.php" class="btn btn-primary">Gestionar Centros Médicos</a>
            <a href="crud_medicos.php" class="btn btn-primary">Gestionar Médicos</a>
            <a href="busqueda_avanzada.php" class="btn btn-warning">Búsqueda Avanzada de Citas</a>
        </div>

        <div class="row">
            <!-- Tabla de Centros Médicos -->
            <div class="col-md-7">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-secondary text-white"><h5 class="mb-0">Estadísticas por Centro Médico</h5></div>
                    <div class="card-body">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr><th>Centro Médico</th><th>Total Citas</th><th>Inasistencias</th><th>% Inasistencia</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach($estadisticas_centros as $centro): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($centro['centro']) ?></td>
                                        <td><?= $centro['total_citas'] ?></td>
                                        <td><?= $centro['total_inasistencias'] ?></td>
                                        <td>
                                            <span class="badge <?= ($centro['porcentaje_inasistencia'] > 20) ? 'bg-danger' : 'bg-success' ?>">
                                                <?= $centro['porcentaje_inasistencia'] !== null ? $centro['porcentaje_inasistencia'] . '%' : '0%' ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tabla de Diagnósticos -->
            <div class="col-md-5">
                <div class="card shadow-sm mb-4">
                    <div class="card-header bg-danger text-white"><h5 class="mb-0">Top 5 Diagnósticos (CIE-10)</h5></div>
                    <div class="card-body">
                        <table class="table table-bordered">
                            <thead>
                                <tr><th>Código</th><th>Descripción</th><th>Casos</th></tr>
                            </thead>
                            <tbody>
                                <?php foreach($top_diagnosticos as $diag): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($diag['codigo_cie10']) ?></strong></td>
                                        <td><?= htmlspecialchars($diag['descripcion']) ?></td>
                                        <td><?= $diag['frecuencia'] ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
