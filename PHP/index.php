<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - SaludUSM</title>
    <!-- Bootstrap para diseño ordenado (Bono 5 puntos) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light d-flex align-items-center justify-content-center" style="height: 100vh;">
    <div class="card shadow p-4" style="width: 100%; max-width: 400px;">
        <h2 class="text-center text-primary mb-4">SaludUSM</h2>
        
        <?php if(isset($_GET['error'])): ?>
            <div class="alert alert-danger">Credenciales incorrectas o rol inválido.</div>
        <?php endif; ?>
        
        <?php if(isset($_GET['mensaje']) && $_GET['mensaje'] == 'cuenta_eliminada'): ?>
            <div class="alert alert-info">Tu cuenta ha sido eliminada correctamente.</div>
        <?php endif; ?>

        <form action="procesar_login.php" method="POST">
            <div class="mb-3">
                <label for="rut" class="form-label">RUT o Usuario (Ej: 11111111-1 o admin)</label>
                <input type="text" class="form-control" id="rut" name="rut" required>
            </div>
            <div class="mb-3">
                <label for="clave" class="form-label">Contraseña</label>
                <input type="password" class="form-control" id="clave" name="clave" required>
            </div>
            <div class="mb-3">
                <label for="rol" class="form-label">Rol</label>
                <select class="form-select" id="rol" name="rol" required>
                    <option value="1">Paciente</option>
                    <option value="2">Médico</option>
                    <option value="3">Administrador</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary w-100">Ingresar</button>
        </form>
        
        <div class="mt-3 text-center">
            <a href="registro.php" class="text-decoration-none">¿Eres paciente nuevo? Regístrate aquí</a>
        </div>
    </div>
</body>
</html>
