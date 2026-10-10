<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 3) { header("Location: index.php"); exit(); }

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['crear_medico'])) {
    try {
        $hash_clave = password_hash($_POST['rut'], PASSWORD_DEFAULT); // Clave por defecto es el RUT
        $stmt = $pdo->prepare("INSERT INTO Medico (rut, nombre_completo, correo, clave) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_POST['rut'], $_POST['nombre'], $_POST['correo'], $hash_clave]);
        $mensaje = "<div class='alert alert-success'>Médico registrado. Clave temporal: su RUT. Recuerde asignarle especialidades y centros en la BD.</div>";
    } catch (PDOException $e) {
        $mensaje = "<div class='alert alert-danger'>Error: " . $e->getMessage() . "</div>";
    }
}

$medicos = $pdo->query("SELECT * FROM Medico")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>CRUD Médicos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light container mt-5">
    <a href="admin_dashboard.php" class="btn btn-secondary mb-3">Volver al Panel</a>
    <?= $mensaje ?>

    <div class="card shadow-sm mb-4">
        <div class="card-header bg-primary text-white"><h5 class="mb-0">Registrar Médico</h5></div>
        <div class="card-body">
            <form method="POST" class="row g-3">
                <input type="hidden" name="crear_medico" value="1">
                <div class="col-md-3">
                    <input type="text" name="rut" class="form-control" placeholder="RUT (12345678-9)" required>
                </div>
                <div class="col-md-4">
                    <input type="text" name="nombre" class="form-control" placeholder="Nombre Completo" required>
                </div>
                <div class="col-md-4">
                    <input type="email" name="correo" class="form-control" placeholder="Correo Electrónico" required>
                </div>
                <div class="col-md-1">
                    <button type="submit" class="btn btn-success w-100">+</button>
                </div>
            </form>
        </div>
    </div>

    <table class="table table-bordered bg-white">
        <thead><tr><th>RUT</th><th>Nombre</th><th>Correo</th></tr></thead>
        <tbody>
            <?php foreach($medicos as $m): ?>
                <tr>
                    <td><?= htmlspecialchars($m['rut']) ?></td>
                    <td><?= htmlspecialchars($m['nombre_completo']) ?></td>
                    <td><?= htmlspecialchars($m['correo']) ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>
</html>
