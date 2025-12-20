<?php
session_start();
require '../config/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php?vista=contabilidad');
    exit;
}

$idConsulta  = $_POST['id_paciente'] ?? null; // aquí viene el ID de la consulta
$idUsuario   = $_SESSION['usuario']['id'] ?? null;
$tipoPago    = $_POST['tipo_pago'] ?? null;
$montoPagar  = isset($_POST['monto_pagar']) ? (float) $_POST['monto_pagar'] : 0;

if (!$idConsulta || !$tipoPago) {
    die('Datos incompletos');
}

try {
    $pdo->beginTransaction();

    /* ======================================================
       CONSULTA
    ====================================================== */
    $stmt = $pdo->prepare("
        SELECT id, id_paciente, precio, pagado
        FROM consultas
        WHERE id = ?
        FOR UPDATE
    ");
    $stmt->execute([$idConsulta]);
    $consulta = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$consulta) {
        throw new Exception('Consulta no encontrada');
    }

    if ((int)$consulta['pagado'] === 1) {
        throw new Exception('La consulta ya está pagada');
    }

    $consultaId  = (int)$consulta['id'];
    $pacienteId  = (int)$consulta['id_paciente'];
    $precioTotal = (float)$consulta['precio'];

    /* ======================================================
       BUSCAR PRÉSTAMO ACTIVO (SI EXISTE)
    ====================================================== */
    $stmt = $pdo->prepare("
        SELECT id, total, estado
        FROM prestamos
        WHERE origen_tipo = 'CONSULTA'
          AND origen_id = ?
          AND estado != 'PAGADO'
        LIMIT 1
        FOR UPDATE
    ");
    $stmt->execute([$consultaId]);
    $prestamo = $stmt->fetch(PDO::FETCH_ASSOC);

    $montoPendiente = $prestamo ? (float)$prestamo['total'] : $precioTotal;

    /* ======================================================
       EFECTIVO
    ====================================================== */
    if ($tipoPago === 'efectivo') {

        // Si había préstamo, se salda
        if ($prestamo) {
            $pdo->prepare("
                UPDATE prestamos
                SET total = 0, estado = 'PAGADO'
                WHERE id = ?
            ")->execute([$prestamo['id']]);
        }

        $pdo->prepare("UPDATE consultas SET pagado = 1 WHERE id = ?")
            ->execute([$consultaId]);

        $pdo->commit();
        header('Location: ../index.php?vista=contabilidad&ok=efectivo');
        exit;
    }

    /* ======================================================
       SEGURO
    ====================================================== */
    if ($tipoPago === 'seguro') {

        // Buscar seguro
        $stmt = $pdo->prepare("
            SELECT s.id, s.saldo_actual
            FROM seguros s
            LEFT JOIN seguros_beneficiarios sb ON sb.seguro_id = s.id
            WHERE s.titular_id = ? OR sb.paciente_id = ?
            LIMIT 1
            FOR UPDATE
        ");
        $stmt->execute([$pacienteId, $pacienteId]);
        $seguro = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$seguro) {
            throw new Exception('El paciente no tiene seguro');
        }

        $seguroId    = (int)$seguro['id'];
        $saldoSeguro = (float)$seguro['saldo_actual'];

        $montoCubierto = min($saldoSeguro, $montoPendiente);
        $nuevoPendiente = $montoPendiente - $montoCubierto;

        // Descontar seguro
        $pdo->prepare("
            UPDATE seguros SET saldo_actual = saldo_actual - ?
            WHERE id = ?
        ")->execute([$montoCubierto, $seguroId]);

        // Movimiento seguro
        $pdo->prepare("
            INSERT INTO movimientos_seguro
            (seguro_id, paciente_id, tipo, monto, descripcion)
            VALUES (?,?,?,?,?)
        ")->execute([
            $seguroId,
            $pacienteId,
            'DEBITO',
            $montoCubierto,
            'Pago consulta con seguro'
        ]);

        if ($nuevoPendiente <= 0) {

            // Todo cubierto
            if ($prestamo) {
                $pdo->prepare("
                    UPDATE prestamos SET total = 0, estado = 'PAGADO'
                    WHERE id = ?
                ")->execute([$prestamo['id']]);
            }

            $pdo->prepare("UPDATE consultas SET pagado = 1 WHERE id = ?")
                ->execute([$consultaId]);

        } else {

            // Crear o actualizar préstamo
            if ($prestamo) {
                $pdo->prepare("
                    UPDATE prestamos
                    SET total = ?, estado = 'PARCIAL'
                    WHERE id = ?
                ")->execute([$nuevoPendiente, $prestamo['id']]);
            } else {
                $pdo->prepare("
                    INSERT INTO prestamos
                    (paciente_id, total, estado, fecha, origen_tipo, origen_id)
                    VALUES (?,?,?,?,?,?)
                ")->execute([
                    $pacienteId,
                    $nuevoPendiente,
                    'PARCIAL',
                    date('Y-m-d'),
                    'CONSULTA',
                    $consultaId
                ]);
            }

            $pdo->prepare("UPDATE consultas SET pagado = 0 WHERE id = ?")
                ->execute([$consultaId]);
        }

        $pdo->commit();
        header('Location: ../index.php?vista=contabilidad&ok=seguro');
        exit;
    }

    /* ======================================================
       A DEUDO / PRÉSTAMO
    ====================================================== */
    if ($tipoPago === 'prestamo') {

        $montoPagar = max(0, min($montoPagar, $montoPendiente));
        $nuevoPendiente = $montoPendiente - $montoPagar;

        if ($prestamo) {
            $pdo->prepare("
                UPDATE prestamos
                SET total = ?, estado = ?
                WHERE id = ?
            ")->execute([
                $nuevoPendiente,
                $nuevoPendiente <= 0 ? 'PAGADO' : 'PARCIAL',
                $prestamo['id']
            ]);
        } else {
            $pdo->prepare("
                INSERT INTO prestamos
                (paciente_id, total, estado, fecha, origen_tipo, origen_id)
                VALUES (?,?,?,?,?,?)
            ")->execute([
                $pacienteId,
                $nuevoPendiente,
                $montoPagar > 0 ? 'PARCIAL' : 'PENDIENTE',
                date('Y-m-d'),
                'CONSULTA',
                $consultaId
            ]);
        }

        if ($nuevoPendiente <= 0) {
            $pdo->prepare("UPDATE consultas SET pagado = 1 WHERE id = ?")
                ->execute([$consultaId]);
        }

        $pdo->commit();
        header('Location: ../index.php?vista=contabilidad&ok=prestamo');
        exit;
    }

    throw new Exception('Tipo de pago no válido');

} catch (Exception $e) {
    $pdo->rollBack();
    die('Error: ' . $e->getMessage());
}
