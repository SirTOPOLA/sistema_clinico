<?php
if (session_status() == PHP_SESSION_NONE) {
  // Si la sesión no está iniciada, se inicia
  session_start();
}
require_once 'config/conexion.php';
require_once 'auth/auth.php';

// Si ya está logueado, redirige
if (isset($_SESSION['usuario'])) {
  header('Location: index.php?vista=dashboard');
  exit;
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // Sanitización básica
  $tipo = isset($_POST['tipo_usuario']) ? trim($_POST['tipo_usuario']) : 'personal';
  $email = isset($_POST['correo']) ? filter_var(trim($_POST['correo']), FILTER_SANITIZE_EMAIL) : '';
  $password = isset($_POST['contrasena']) ? trim($_POST['contrasena']) : '';
  $clienteId = isset($_POST['codigo']) ? trim($_POST['codigo']) : '';



  // Validar tipo de usuario (opcional: según un conjunto permitido)
  $tipos_validos = ['personal', 'cliente']; // por ejemplo
  if (!in_array($tipo, $tipos_validos)) {
    $_SESSION['alerta'] = "Tipo de usuario inválido.";
  }



  // Validaciones básicas
  if ($tipo === 'cliente') {
    // Validar clienteId si el tipo es "cliente"
    if ($tipo === 'cliente' && empty($clienteId)) {
      $_SESSION['alerta'] = "Código de cliente inválido.";
    }

  } else {
    // Validar email
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $_SESSION['alerta'] = "Correo electrónico inválido.";
    }

    // Validar contraseña (ejemplo: mínimo 6 caracteres)
    if (empty($password) || strlen($password) < 1) {
      $_SESSION['alerta'] = "La contraseña debe tener al menos 6 caracteres.";
    }
  }

  // Mostrar errores si existen
  if (!empty($errores)) {
    /* foreach ($errores as $error) {
        header('Location: login.php'); 
        exit;
    } */
    $_SESSION['alerta'] = 'hubo errores';
    exit;
  }

  // Si no hay errores, intentamos login según tipo


  if ($tipo === 'cliente') {
    // loginCliente() debe validar cliente por código
    if (loginCliente($pdo, $clienteId)) {
      header('Location: index.php');
      exit;
    } else {

      header('Location: login.php');
      exit;
    }

  } else {
    // Usuario interno (admin, operario, etc.)
    if (login($pdo, $email, $password)) {
      header('Location: index.php');
      exit;
    } else {
      // Mensaje de error ya asignado por login()
      header('Location: login.php');
      exit;
    }
  }

}

/* ********* CONFIGURACION ****** */
$stmt = $pdo->prepare("SELECT * FROM configuracion");
$stmt->execute();
$configuracion = $stmt->fetch();

if ($configuracion !== false) {
  $nombre_empresa = $configuracion['nombre_empresa'];
  $direccion = $configuracion['direccion'];
  $mision = $configuracion['mision'];
  $vision = $configuracion['vision'];
  $telefono = $configuracion['telefono'];
  $correo = $configuracion['correo'];
  $logo = $configuracion['logo'];
  $historia = $configuracion['historia']; // o historia, según tu DB
  $imagen_portada = $configuracion['imagen'];
} else {
  // No hay configuración en la BD
  $nombre_empresa = '';
  $logo = '';
  $correo = '';
  $direccion = '';
  $mision = '';
  $vision = '';
  $historia = '';
  $imagen_portada = '';
}


?>



<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login – <?= htmlspecialchars($nombre_empresa) ?></title>

  <!-- Bootstrap y Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet" />
  <link rel="stylesheet" href="./assets/css/public/login.css">
  <style>
    /* login.css */

    html,
    body {
      height: 100%;
      margin: 0;
      background-color: #f8f9fa;
    }

    .login-wrapper {
      height: 100vh;
    }

    .login-card {
      width: 100%;
      max-width: 100%;
      height: 100%;
      box-shadow: 0 0 30px rgba(0, 0, 0, 0.1);
      background-color: white;
      border-radius: 1rem;
      overflow: hidden;
    }

    .login-image {
      background: url('api/<?= $imagen_portada ?>') no-repeat center center;
      background-size: cover;
      height: 300px;
    }

    @media (min-width: 768px) {
      .login-image {
        height: 100%;
      }
    }

    .form-label-icon {
      display: flex;
      align-items: center;
      font-weight: 600;
      font-size: 0.95rem;
      gap: 0.5rem;
      color: #495057;
    }

    .form-control,
    .input-group-text {
      border-radius: 0.5rem;
      border: 1px solid #ced4da;
    }

    .form-control:focus {
      box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .15);
      border-color: #80bdff;
    }

    .btn-toggle-option {
      font-size: 0.9rem;
      border-radius: 2rem;
     /*  padding: 0.6rem 1.2rem; */
    }

    .btn-submit {
      padding: 0.6rem;
      font-weight: 600;
      border-radius: 2rem;
    }

    .form-container {
      
      
      background: #ffffff;
      border-radius: 1rem;
      padding: 2rem;
      box-shadow: 0 0 20px rgba(0, 0, 0, 0.05);
    }
  </style>
</head>

<body>

  <div
    class="container-fluid d-flex flex-column flex-md-row align-items-center justify-content-center p-0 login-wrapper">
    <!-- Card completa en móvil y solo form en escritorio -->
    <div class="login-card d-flex flex-column flex-md-row overflow-hidden">

      <!-- Imagen -->
      <div class="login-image d-block d-md-none"></div> <!-- visible solo en móviles -->
      <div class="col-md-6 d-none d-md-block p-0">
        <div class="login-image"></div>
      </div>

      <!-- Formulario -->
      <div class="col-md-6  d-flex align-items-center justify-content-center form-container">
        <div class="login-form">
          <h3 class="mb-4 text-center text-success fw-semibold"> <span
              class="fw-bold"><?= htmlspecialchars($nombre_empresa) ?> </span>
          </h3>
          <?php include_once('components/alerta.php') ?>
          <form method="POST" id="formLogin" novalidate>
            <!-- Selector tipo usuario -->
            <div class="mb-4">
              <label class="form-label-icon mb-2"><i class="bi bi-person-lines-fill text-secondary"></i> Tipo de
                usuario</label>
              <div class="d-flex flex-column flex-md-row gap-2">
                <input type="radio" class="btn-check" name="tipo_usuario" id="tipo_personal" value="personal"
                  autocomplete="off" checked>
                <label class="btn btn-outline-success w-100 btn-toggle-option" for="tipo_personal">
                  <i class="bi bi-people-fill"></i> Personal  
                </label>

                <input type="radio" class="btn-check" name="tipo_usuario" id="tipo_cliente" value="cliente"
                  autocomplete="off">
                <label class="btn btn-outline-primary w-100 btn-toggle-option" for="tipo_cliente">
                  <i class="bi bi-person-badge-fill"></i> Cliente
                </label>
              </div>
            </div>

            <!-- Campos Personal -->
            <div id="personal_fields">
              <div class="mb-3">
                <label for="usuario" class="form-label-icon">
                  <i class="bi bi-envelope-at text-success"></i> Correo electrónico
                </label>
                <div class="input-group">
                  <span class="input-group-text bg-white"><i class="bi bi-person text-success"></i></span>
                  <input type="email" id="usuario" name="correo" class="form-control" placeholder="correo@ejemplo.com"
                    required>
                </div>
              </div>

              <div class="mb-3">
                <label for="clave" class="form-label-icon">
                  <i class="bi bi-shield-lock text-success"></i> Contraseña
                </label>
                <div class="input-group">
                  <span class="input-group-text bg-white"><i class="bi bi-lock text-success"></i></span>
                  <input type="password" id="clave" name="contrasena" class="form-control" placeholder="••••••••"
                    required>
                </div>
              </div>
            </div>

            <!-- Campos Cliente -->
            <div id="cliente_fields" style="display:none;">
              <div class="mb-3">
                <label for="codigo" class="form-label-icon">
                  <i class="bi bi-person-vcard text-primary"></i> ID de Cliente
                </label>
                <div class="input-group">
                  <span class="input-group-text bg-white"><i class="bi bi-person-badge text-primary"></i></span>
                  <input type="text" id="codigo" name="codigo" class="form-control" placeholder="Código de cliente"
                    required>
                </div>
              </div>
            </div>

            <!-- Botón enviar -->
            <div class="d-grid">
              <button type="submit" class="btn btn-primary btn-submit">Acceder</button>
            </div>
          </form>

          <div class="text-center mt-3">
            <a href="index.php?vista=inicio">← Volver a Inicio</a>
          </div>
        </div>
      </div>

    </div>
  </div>



  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const tipoPersonal = document.getElementById('tipo_personal');
      const tipoCliente = document.getElementById('tipo_cliente');
      const personalFields = document.getElementById('personal_fields');
      const clienteFields = document.getElementById('cliente_fields');

      tipoPersonal.addEventListener('change', function () {
        if (this.checked) {
          personalFields.style.display = 'block';
          clienteFields.style.display = 'none';
        }
      });

      tipoCliente.addEventListener('change', function () {
        if (this.checked) {
          personalFields.style.display = 'none';
          clienteFields.style.display = 'block';
        }
      });
    });
  </script>

</body>

</html>