<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 3) { header("Location: index.php"); exit(); }

$mensaje = '';


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['crear'])) {
    try {
        $stmt = $pdo->prepare("INSERT INTO Centro_Medico (nombre, comuna, region) VALUES (?, ?, ?)");
        $stmt->execute([$_POST['nombre'], $_POST['comuna'], $_POST['region']]);
        $mensaje = "<div class='alert alert-success'>Centro creado exitosamente.</div>";
    } catch (PDOException $e) {
        $mensaje = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
}


if (isset($_GET['eliminar'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM Centro_Medico WHERE id_centro = ?");
        $stmt->execute([$_GET['eliminar']]);
        $mensaje = "<div class='alert alert-success'>Centro eliminado.</div>";
    } catch (PDOException $e) {
        $mensaje = "<div class='alert alert-danger'>No se puede eliminar: tiene registros asociados.</div>";
    }
}

$centros = $pdo->query("SELECT * FROM Centro_Medico")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>CRUD Centros Médicos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light container mt-5">
    <a href="admin_dashboard.php" class="btn btn-secondary mb-3">Volver al Panel</a>
    <?= $mensaje ?>
    
    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white"><h5 class="mb-0">Agregar Centro Médico</h5></div>
        <div class="card-body">
            <form method="POST" class="row g-3">
                <input type="hidden" name="crear" value="1">
                <div class="col-md-4">
                    <input type="text" name="nombre" class="form-control" placeholder="Nombre del Centro" required>
                </div>
                <div class="col-md-4">
                    <input type="text" name="comuna" class="form-control" placeholder="Comuna" required>
                </div>
                <div class="col-md-3">
                    <input type="text" name="region" class="form-control" placeholder="Región" required>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-success w-100">+</button>
                </div>
            </form>
        </div>
    </div>

    <table class="table table-bordered bg-white">
        <thead><tr><th>ID</th><th>Nombre</th><th>Comuna</th><th>Región</th><th>Acciones</th></tr></thead>
        <tbody>
            <?php foreach($centros as $c): ?>
                <tr>
                    <td><?= $c['id_centro'] ?></td>
                    <td><?= htmlspecialchars($c['nombre']) ?></td>
                    <td><?= htmlspecialchars($c['comuna']) ?></td>
                    <td><?= htmlspecialchars($c['region']) ?></td>
                    <td>
                        <a href="?eliminar=<?= $c['id_centro'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar centro?');">Eliminar</a>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
