<?php
require 'conexion.php';

$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rut = $_POST['rut'];
    $nombre = $_POST['nombre_completo'];
    $fecha_nac = $_POST['fecha_nac'];
    $sexo = $_POST['sexo'];
    $id_prevision = $_POST['id_prevision'];
    $telefono = $_POST['telefono'];
    $comuna = $_POST['comuna'];
    $clave = $_POST['clave'];
    $confirmar_clave = $_POST['confirmar_clave'];

    // VALIDAR EL RUT VIA FORMA REGULAR 
    if (!preg_match("/^[0-9]{7,8}-[0-9Kk]{1}$/", $rut)) {
        $mensaje = "<div class='alert alert-danger'>El formato del RUT es inválido. Debe ser XXXXXXXX-X.</div>";
    } elseif ($clave !== $confirmar_clave) {
        $mensaje = "<div class='alert alert-danger'>Las contraseñas no coinciden.</div>";
    } elseif (strlen($clave) < 6) {
        $mensaje = "<div class='alert alert-danger'>La contraseña debe tener al menos 6 caracteres.</div>";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT rut FROM Paciente WHERE rut = ?");
            $stmt->execute([$rut]);
            if ($stmt->rowCount() > 0) {
                $mensaje = "<div class='alert alert-danger'>El RUT ingresado ya está registrado.</div>";
            } else {
                $hash_clave = password_hash($clave, PASSWORD_DEFAULT);
                $sql = "INSERT INTO Paciente (rut, nombre_completo, fecha_nac, sexo, id_prevision, telefono, comuna, clave) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$rut, $nombre, $fecha_nac, $sexo, $id_prevision, $telefono, $comuna, $hash_clave]);
                
                $mensaje = "<div class='alert alert-success'>Registro exitoso. <a href='index.php'>Inicie sesión aquí</a>.</div>";
            }
        } catch (PDOException $e) {
            $mensaje = "<div class='alert alert-danger'>Error en el registro: " . $e->getMessage() . "</div>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Paciente</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">Registro de Paciente Nuevo</h4>
                </div>
                <div class="card-body">
                    <?= $mensaje ?>
                    <form method="POST">
                        <div class="mb-3">
                            <label>RUT (Ej: 12345678-9)</label>
                            <input type="text" name="rut" class="form-control" required pattern="[0-9]{7,8}-[0-9Kk]{1}" title="Formato: 12345678-9">
                        </div>
                        <div class="mb-3">
                            <label>Nombre Completo</label>
                            <input type="text" name="nombre_completo" class="form-control" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Fecha de Nacimiento</label>
                                <input type="date" name="fecha_nac" class="form-control" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Sexo</label>
                                <select name="sexo" class="form-select" required>
                                    <option value="M">Masculino</option>
                                    <option value="F">Femenino</option>
                                    <option value="Otro">Otro</option>
                                </select>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Previsión</label>
                                <select name="id_prevision" class="form-select" required>
                                    <option value="1">Fonasa</option>
                                    <option value="2">Isapre</option>
                                    <option value="3">Particular</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Teléfono</label>
                                <input type="text" name="telefono" class="form-control">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label>Comuna</label>
                            <input type="text" name="comuna" class="form-control" required>
                        </div>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label>Contraseña (Mín. 6 caracteres)</label>
                                <input type="password" name="clave" class="form-control" required minlength="6">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label>Confirmar Contraseña</label>
                                <input type="password" name="confirmar_clave" class="form-control" required minlength="6">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Registrarme</button>
                        <a href="index.php" class="btn btn-link w-100 mt-2">Volver al Login</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
