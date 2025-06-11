<?php
 $stmt = $pdo->query("SELECT COUNT(*) FROM usuarios");
 if ($stmt->fetchColumn() == 0) {
     header("Location: setup_inicial.php");
     exit;
 }


?>