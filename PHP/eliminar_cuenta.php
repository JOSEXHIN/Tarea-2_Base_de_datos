<?php
session_start();
require 'conexion.php';

if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 1) { header("Location: index.php"); exit(); }

try {
    $stmt = $pdo->prepare("DELETE FROM Paciente WHERE id_paciente = ?");
    $stmt->execute([$_SESSION['usuario_id']]);
    session_destroy();
    header("Location: index.php?mensaje=cuenta_eliminada");
} catch (PDOException $e) { die("Error: " . $e->getMessage()); }
?>
