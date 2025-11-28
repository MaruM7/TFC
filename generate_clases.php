<?php
// generate_clases.php
require_once __DIR__ . '/config.php';
date_default_timezone_set('Europe/Madrid'); // Asegura la zona horaria correcta

// Rango de tiempo a generar (Ej: los próximos 30 días)
$dias_a_generar = 30;

// Obtener todas las plantillas de clases recurrentes activas
$plantillas = $pdo->query('SELECT * FROM clases_recurrencia WHERE activo = 1')->fetchAll();

if (!$plantillas) {
    die('No hay plantillas de clases activas.');
}

$hoy = new DateTime();

foreach ($plantillas as $p) {
    // Calcular el número del día de la semana (1=Lunes, 7=Domingo)
    $dia_semana_num = array_search($p['dia_semana'], ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo']) + 1;
    
    // Iterar sobre los próximos 30 días
    for ($i = 0; $i <= $dias_a_generar; $i++) {
        $fecha_iteracion = clone $hoy;
        $fecha_iteracion->modify("+$i days");
        
        // Comprobar si la fecha de iteración coincide con el día de la semana de la plantilla
        if ($fecha_iteracion->format('N') == $dia_semana_num) {
            
            // Construir la fecha y hora completa
            $fecha_hora = $fecha_iteracion->format('Y-m-d') . ' ' . $p['hora_inicio'];
            
            // 1. Comprobar si esta clase específica ya existe
            $stmt_check = $pdo->prepare('SELECT id FROM clases WHERE disciplina_id = ? AND fecha_hora = ?');
            $stmt_check->execute([$p['disciplina_id'], $fecha_hora]);
            
            if (!$stmt_check->fetch()) {
                // 2. Insertar la clase si no existe
                $stmt_insert = $pdo->prepare('
                    INSERT INTO clases 
                    (disciplina_id, instructor_id, fecha_hora, duracion, descripcion, cupo) 
                    VALUES (?, ?, ?, ?, ?, ?)
                ');
                $stmt_insert->execute([
                    $p['disciplina_id'], 
                    $p['instructor_id'], 
                    $fecha_hora, 
                    $p['duracion'], 
                    $p['descripcion'], 
                    $p['cupo']
                ]);
                echo "Clase generada: $fecha_hora\n";
            }
        }
    }
}

echo "Proceso de generación finalizado.\n";
?>