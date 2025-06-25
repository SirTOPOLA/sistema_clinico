<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once '../config/conexion.php'; // Debe usar PDO y try-catch en esa conexión

$id_paciente = filter_input(INPUT_GET, 'id_paciente', FILTER_VALIDATE_INT);
$inicio = filter_input(INPUT_GET, 'inicio', FILTER_SANITIZE_STRING);
$fin = filter_input(INPUT_GET, 'fin', FILTER_SANITIZE_STRING);

if (!$id_paciente) {
    http_response_code(400);
    echo "<div class='alert alert-warning'>Paciente no válido</div>";
    exit;
}

try {
    $params = [$id_paciente];
    $cond = "";

    if ($inicio && $fin) {
        $cond = "AND c.fecha_registro BETWEEN ? AND ?";
        $params[] = "$inicio 00:00:00";
        $params[] = "$fin 23:59:59";
    }

    $sql = "SELECT * FROM consultas c
            WHERE c.id_paciente = ? $cond
            ORDER BY c.fecha_registro DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $consultas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!$consultas) {
        echo "<p class='text-muted text-center'>No hay historial disponible en este rango de fechas.</p>";
        exit;
    }

    foreach ($consultas as $c) {
        echo "<div class='card mb-3 shadow-sm'>";
        echo "<div class='card-header bg-primary text-white'>
                <strong>Consulta:</strong> " . htmlspecialchars(date("d/m/Y H:i", strtotime($c['fecha_registro']))) . "
              </div>";
        echo "<div class='card-body'>";
        echo "<p><strong>Motivo:</strong> " . nl2br(htmlspecialchars($c['motivo_consulta'])) . "</p>";

        echo "<p><strong>Signos Vitales:</strong></p><ul>";
        $vitales = [
            'Temperatura' => $c['temperatura'] . '°C',
            'Frecuencia Cardíaca' => $c['frecuencia_cardiaca'] . ' bpm',
            'Frecuencia Respiratoria' => $c['frecuencia_respiratoria'] . ' rpm',
            'Tensión Arterial' => $c['tension_arterial'],
            'Pulso' => $c['pulso'] . ' bpm',
            'Saturación Oxígeno' => $c['saturacion_oxigeno'] . '%',
            'Peso Actual' => $c['peso_actual'] . " kg (IMC: {$c['imc']})"
        ];
        foreach ($vitales as $k => $v) echo "<li><strong>$k:</strong> " . htmlspecialchars($v) . "</li>";
        echo "</ul>";

        // Detalles de consulta
        $det = $pdo->prepare("SELECT * FROM detalle_consulta WHERE id_consulta = ?");
        $det->execute([$c['id']]);
        $detalle = $det->fetch(PDO::FETCH_ASSOC);

        if ($detalle) {
            echo "<hr><p><strong>Detalles:</strong></p><ul>";
            $detalles = [
                'Orina' => $detalle['orina'],
                'Defeca' => $detalle['defeca'] . " ({$detalle['defeca_dias']} días)",
                'Duerme' => $detalle['duerme'] . " ({$detalle['duerme_horas']} h)",
                'Operación' => $detalle['operacion'],
                'Alergias' => $detalle['alergico'],
                'Antecedentes patológicos' => $detalle['antecedentes_patologicos'],
                'Antecedentes familiares' => $detalle['antecedentes_familiares'],
                'Antecedentes cónyuge' => $detalle['antecedentes_conyuge'],
                'Signos vitales adicionales' => $detalle['control_signos_vitales']
            ];
            foreach ($detalles as $k => $v) echo "<li><strong>$k:</strong> " . htmlspecialchars($v) . "</li>";
            echo "</ul>";
        }

        // Analíticas
        $an = $pdo->prepare("SELECT a.resultado, a.valores_refencia, t.nombre AS tipo
                             FROM analiticas a
                             JOIN tipo_pruebas t ON a.id_tipo_prueba = t.id
                             WHERE a.id_consulta = ?");
        $an->execute([$c['id']]);
        $analiticas = $an->fetchAll(PDO::FETCH_ASSOC);

        if ($analiticas) {
            echo "<hr><p><strong>Analíticas:</strong></p><ul>";
            foreach ($analiticas as $a) {
                echo "<li><strong>" . htmlspecialchars($a['tipo']) . ":</strong> " . htmlspecialchars($a['resultado']) .
                     "<br><small class='text-muted'>Referencia: " . htmlspecialchars($a['valores_refencia']) . "</small></li>";
            }
            echo "</ul>";
        }

        // Recetas
        $re = $pdo->prepare("SELECT descripcion, comentario FROM recetas WHERE id_consulta = ?");
        $re->execute([$c['id']]);
        $recetas = $re->fetchAll(PDO::FETCH_ASSOC);

        if ($recetas) {
            echo "<hr><p><strong>Recetas:</strong></p>";
            foreach ($recetas as $r) {
                echo "<div class='border rounded p-2 mb-2'>";
                echo "<p>" . nl2br(htmlspecialchars($r['descripcion'])) . "</p>";
                if (!empty($r['comentario'])) {
                    echo "<small class='text-muted'>Comentario: " . htmlspecialchars($r['comentario']) . "</small>";
                }
                echo "</div>";
            }
        }

        echo "</div></div>"; // Fin card
    }

} catch (Exception $e) {
    http_response_code(500);
    echo "<div class='alert alert-danger'>Error interno al cargar el historial.</div>";
    error_log($e->getMessage());
}
?>
