<?php
session_start();
require 'conexion.php';

// Validar rol de Médico
if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 2) { 
    header("Location: index.php"); 
    exit(); 
}

$id_medico = $_SESSION['usuario_id'];
$id_cita = $_GET['id_cita'] ?? null;
$mensaje = '';

if (!$id_cita) {
    header("Location: medico_dashboard.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recibir datos del formulario
    $estado_cita = $_POST['estado_cita'];
    $motivo = $_POST['motivo_consulta'];
    $observaciones = $_POST['observaciones'];
    $cie10_codigo = $_POST['cie10_codigo'];
    $cie10_desc = $_POST['cie10_desc'];
    $medicamento = $_POST['medicamento'];
    $dosis = $_POST['dosis'];
    $dias = $_POST['dias'];

    try {
        $pdo->beginTransaction();

        // Actualizar el estado de la cita
        $stmt = $pdo->prepare("UPDATE Cita SET id_estado = ? WHERE id_cita = ? AND id_medico = ?");
        $stmt->execute([$estado_cita, $id_cita, $id_medico]);

        //Si el estado es Atendida, se inserta la ficha
        if ($estado_cita == 3) { 
            // Insertar Atención
            $stmtAtencion = $pdo->prepare("INSERT INTO Atencion (id_cita, motivo_consulta, observaciones) VALUES (?, ?, ?)");
            $stmtAtencion->execute([$id_cita, $motivo, $observaciones]);
            $id_atencion = $pdo->lastInsertId();

            // Insertar Diagnóstico CIE-10
            if (!empty($cie10_codigo) && !empty($cie10_desc)) {
                $stmtDiag = $pdo->prepare("INSERT INTO Diagnostico (id_atencion, codigo_cie10, descripcion) VALUES (?, ?, ?)");
                $stmtDiag->execute([$id_atencion, $cie10_codigo, $cie10_desc]);
            }

            // Insertar Receta
            if (!empty($medicamento)) {
                $stmtReceta = $pdo->prepare("INSERT INTO Receta (id_atencion, medicamento, dosis, dias_tratamiento) VALUES (?, ?, ?, ?)");
                $stmtReceta->execute([$id_atencion, $medicamento, $dosis, $dias]);
            }
        }

        $pdo->commit();
        $mensaje = "<div class='alert alert-success'>Registro guardado exitosamente. <a href='medico_dashboard.php'>Volver al panel</a></div>";
    } catch (Exception $e) {
        $pdo->rollBack();
        $mensaje = "<div class='alert alert-danger'>Error al guardar: " . $e->getMessage() . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registrar Atención</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="card shadow-sm">
            <div class="card-header bg-success text-white"><h4 class="mb-0">Ficha de Atención Clínica</h4></div>
            <div class="card-body">
                <?= $mensaje ?>
                <form method="POST">
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Estado de la Cita</label>
                            <select name="estado_cita" class="form-select" required>
                                <option value="2">Confirmada</option>
                                <option value="3">Atendida</option>
                                <option value="4">No Asistió</option>
                            </select>
                        </div>
                    </div>
                    
                    <h5 class="mt-4 border-bottom pb-2">Datos Clínicos (Llenar solo si fue Atendida)</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Motivo de Consulta</label>
                            <textarea name="motivo_consulta" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Observaciones</label>
                            <textarea name="observaciones" class="form-control" rows="3"></textarea>
                        </div>
                    </div>

                    <h5 class="mt-4 border-bottom pb-2">Diagnóstico CIE-10</h5>
                    <div class="row mb-3">
                        <div class="col-md-4">
                            <label class="form-label">Código (Ej: J00)</label>
                            <input type="text" name="cie10_codigo" class="form-control">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label">Descripción</label>
                            <input type="text" name="cie10_desc" class="form-control">
                        </div>
                    </div>

                    <h5 class="mt-4 border-bottom pb-2">Receta Médica</h5>
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label">Medicamento</label>
                            <input type="text" name="medicamento" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Dosis</label>
                            <input type="text" name="dosis" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Días de Tratamiento</label>
                            <input type="number" name="dias" class="form-control">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary mt-3">Guardar Registro</button>
                    <a href="medico_dashboard.php" class="btn btn-secondary mt-3">Cancelar</a>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
