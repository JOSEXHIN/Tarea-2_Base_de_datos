<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['rol'])) { 
    header("Location: index.php"); 
    exit(); 
}

$rol = $_SESSION['rol'];
$usuario_id = $_SESSION['usuario_id'];


$where = ["1=1"];
$params = [];


if ($rol == 1) { // Paciente
    $where[] = "c.id_paciente = ?";
    $params[] = $usuario_id;
} elseif ($rol == 2) { // Médico
    $where[] = "c.id_medico = ?";
    $params[] = $usuario_id;
}


if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['buscar'])) {
    if (!empty($_GET['fecha_inicio']) && !empty($_GET['fecha_fin'])) {
        $where[] = "DATE(c.fecha_hora) BETWEEN ? AND ?";
        $params[] = $_GET['fecha_inicio'];
        $params[] = $_GET['fecha_fin'];
    }
    if (!empty($_GET['id_centro'])) {
        $where[] = "c.id_centro = ?";
        $params[] = $_GET['id_centro'];
    }
    if (!empty($_GET['id_estado'])) {
        $where[] = "c.id_estado = ?";
        $params[] = $_GET['id_estado'];
    }
}


$sql = "
    SELECT c.fecha_hora, p.nombre_completo AS paciente, m.nombre_completo AS medico, 
           e.nombre AS especialidad, cm.nombre AS centro, ec.nombre AS estado 
    FROM Cita c
    JOIN Paciente p ON c.id_paciente = p.id_paciente
    JOIN Medico m ON c.id_medico = m.id_medico
    JOIN Especialidad e ON c.id_especialidad = e.id_especialidad
    JOIN Centro_Medico cm ON c.id_centro = cm.id_centro
    JOIN Estado_Cita ec ON c.id_estado = ec.id_estado
    WHERE " . implode(" AND ", $where) . "
    ORDER BY c.fecha_hora DESC
";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);


$centros = $pdo->query("SELECT * FROM Centro_Medico")->fetchAll(PDO::FETCH_ASSOC);
$estados = $pdo->query("SELECT * FROM Estado_Cita")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Búsqueda Avanzada</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-dark text-white"><h5 class="mb-0">Filtros de Búsqueda de Citas</h5></div>
            <div class="card-body">
                <form method="GET" class="row g-3">
                    <input type="hidden" name="buscar" value="1">
                    <div class="col-md-3">
                        <label class="form-label">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" class="form-control" value="<?= $_GET['fecha_inicio'] ?? '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Fecha Fin</label>
                        <input type="date" name="fecha_fin" class="form-control" value="<?= $_GET['fecha_fin'] ?? '' ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Centro Médico</label>
                        <select name="id_centro" class="form-select">
                            <option value="">Todos</option>
                            <?php foreach($centros as $c): ?>
                                <option value="<?= $c['id_centro'] ?>" <?= (isset($_GET['id_centro']) && $_GET['id_centro'] == $c['id_centro']) ? 'selected' : '' ?>><?= $c['nombre'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label">Estado</label>
                        <select name="id_estado" class="form-select">
                            <option value="">Todos</option>
                            <?php foreach($estados as $e): ?>
                                <option value="<?= $e['id_estado'] ?>" <?= (isset($_GET['id_estado']) && $_GET['id_estado'] == $e['id_estado']) ? 'selected' : '' ?>><?= $e['nombre'] ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary">Aplicar Filtros</button>
                        <a href="busqueda_avanzada.php" class="btn btn-secondary">Limpiar</a>
                        <a href="javascript:history.back()" class="btn btn-outline-dark float-end">Volver</a>
                    </div>
                </form>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-header bg-info text-white"><h5 class="mb-0">Resultados</h5></div>
            <div class="card-body">
                <table class="table table-hover">
                    <thead>
                        <tr><th>Fecha y Hora</th><th>Paciente</th><th>Médico</th><th>Especialidad</th><th>Centro</th><th>Estado</th></tr>
                    </thead>
                    <tbody>
                        <?php if(count($resultados) > 0): ?>
                            <?php foreach($resultados as $fila): ?>
                                <tr>
                                    <td><?= date('d-m-Y H:i', strtotime($fila['fecha_hora'])) ?></td>
                                    <td><?= htmlspecialchars($fila['paciente']) ?></td>
                                    <td><?= htmlspecialchars($fila['medico']) ?></td>
                                    <td><?= htmlspecialchars($fila['especialidad']) ?></td>
                                    <td><?= htmlspecialchars($fila['centro']) ?></td>
                                    <td><span class="badge bg-secondary"><?= htmlspecialchars($fila['estado']) ?></span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="6" class="text-center">No se encontraron citas con los filtros aplicados.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
