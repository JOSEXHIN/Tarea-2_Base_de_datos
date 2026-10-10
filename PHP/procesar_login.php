<?php
session_start();
require 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $rut = $_POST['rut'];
    $clave = $_POST['clave'];
    $rol = $_POST['rol'];

    try {
        if ($rol == 1) { // Paciente
            $stmt = $pdo->prepare("SELECT * FROM Paciente WHERE rut = ?");
            $stmt->execute([$rut]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuario && ($clave == $usuario['clave'] || password_verify($clave, $usuario['clave']))) {
                $_SESSION['usuario_id'] = $usuario['id_paciente'];
                $_SESSION['rut'] = $usuario['rut'];
                $_SESSION['nombre'] = $usuario['nombre_completo'];
                $_SESSION['rol'] = 1;
                header("Location: paciente_dashboard.php");
                exit();
            }
        } 
        elseif ($rol == 2) { // Médico
            $stmt = $pdo->prepare("SELECT * FROM Medico WHERE rut = ?");
            $stmt->execute([$rut]);
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if ($usuario && ($clave == $usuario['clave'] || password_verify($clave, $usuario['clave']))) {
                $_SESSION['usuario_id'] = $usuario['id_medico'];
                $_SESSION['rut'] = $usuario['rut'];
                $_SESSION['nombre'] = $usuario['nombre_completo'];
                $_SESSION['rol'] = 2;
                header("Location: medico_dashboard.php");
                exit();
            }
        }
        elseif ($rol == 3) { // Administrador
            if ($rut == 'admin' && $clave == 'admin123') {
                $_SESSION['usuario_id'] = 0;
                $_SESSION['rut'] = 'admin';
                $_SESSION['nombre'] = 'Administrador Sistema';
                $_SESSION['rol'] = 3;
                header("Location: admin_dashboard.php");
                exit();
            }
        }

        header("Location: index.php?error=1");
        exit();

    } catch (PDOException $e) {
        die("Error de consulta: " . $e->getMessage());
    }
} else {
    header("Location: index.php");
}
?>
