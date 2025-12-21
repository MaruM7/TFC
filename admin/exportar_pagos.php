<?php
require_once __DIR__ . '/../config.php';

// Seguridad: Solo admin
if(!isset($_SESSION['usuario']) || $_SESSION['usuario']['rol'] !== 'admin') {
    die('Acceso denegado');
}

// 1. Cabeceras para forzar la descarga del archivo CSV
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename=reporte_pagos_' . date('Y-m-d') . '.csv');

// 2. Abrir la salida de PHP como un archivo
$output = fopen('php://output', 'w');

// 3. Escribir la fila de títulos (Encabezados)
fputcsv($output, ['ID Pago', 'Fecha', 'Nombre Alumno', 'Email', 'Plan', 'Monto (EUR)', 'PayPal ID']);

// 4. Consultar los datos uniendo tablas
$stmt = $pdo->query("
    SELECT p.id, p.fecha_pago, u.nombre, u.apellidos, u.email, p.plan_nombre, p.monto, p.paypal_order_id 
    FROM pagos p 
    JOIN usuarios u ON p.usuario_id = u.id 
    ORDER BY p.fecha_pago DESC
");

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    // Limpiamos los datos para el CSV
    fputcsv($output, [
        $row['id'],
        $row['fecha_pago'],
        $row['nombre'] . ' ' . $row['apellidos'],
        $row['email'],
        $row['plan_nombre'],
        $row['monto'],
        $row['paypal_order_id']
    ]);
}

fclose($output);
exit;