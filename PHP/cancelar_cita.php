<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 1 || !isset($_GET['id'])) { header("Location: index.php"); exit(); }

try {
    $stmt = $pdo->prepare("UPDATE Cita SET id_estado = 5 WHERE id_cita = ? AND id_paciente = ? AND fecha_hora > NOW() AND id_estado IN (1, 2)");
    $stmt->execute([$_GET['id'], $_SESSION['usuario_id']]);
    header("Location: paciente_dashboard.php");
} catch (PDOException $e) { die("Error: " . $e->getMessage()); }
?>
