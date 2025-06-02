<?php
if (session_status() == PHP_SESSION_NONE) {
  // Si la sesión no está iniciada, se inicia
  session_start();
}
require_once 'includes/conexion.php';
require_once 'api/auth.php';

// Si ya está logueado, redirige
if (isset($_SESSION['usuario'])) {
  header('Location: admin/index.php');
  exit;
}


?>


<!DOCTYPE html>
<html lang="es">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Iniciar Sesión | Consultorio Dr. Oscar</title>

  <!-- Bootstrap 5 y Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
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
      background: url('img/img-1.jpg') no-repeat center center;
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

    /* ----figura elegante--- */
    .circle-figure {
  width: 100px;
  height: 100px;
  border-radius: 50%;
  background: linear-gradient(135deg, #198754, #0d6efd); /* Verde a azul */
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
  font-size: 2rem;
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
          <!-- Figura decorativa solo en escritorio -->
 <!--          <div class="round-figure d-none d-md-block"></div> -->
<div class="circle-figure d-none d-lg-flex align-items-center justify-content-center mx-auto mb-3">
  <i class="bi bi-heart-pulse-fill text-white"></i>
</div>

          <h3 class="mb-4 text-center text-success fw-bold"
            style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;">
            Bienvenido al <span class="text-primary">Consultorio Dr. Oscar</span>
          </h3>


          <form method="POST" id="formLogin" novalidate>

            <!-- Campo: Correo -->
            <div class="mb-4">
              <label for="usuario" class="form-label-icon">
                <i class="bi bi-envelope-at text-success"></i> usuario
              </label>
              <div class="input-group">
                <span class="input-group-text bg-white">
                  <i class="bi bi-person text-success"></i>
                </span>
                <input type="text" id="usuario" name="correo" class="form-control" placeholder="correo@ejemplo.com"
                  required autocomplete="text">
              </div>
              <div class="form-text text-danger d-none" id="errorEmail">Ingrese un usuario válido.</div>
            </div>

            <!-- Campo: Contraseña con botón ver -->
            <div class="mb-4">
              <label for="clave" class="form-label-icon">
                <i class="bi bi-shield-lock text-success"></i> Contraseña
              </label>
              <div class="input-group">
                <span class="input-group-text bg-white">
                  <i class="bi bi-lock text-success"></i>
                </span>
                <input type="password" id="clave" name="contrasena" class="form-control" placeholder="••••••••"
                  minlength="6" required autocomplete="current-password">
                <button type="button" class="btn btn-outline-secondary" onclick="togglePassword()" tabindex="-1">
                  <i class="bi bi-eye" id="toggleIcon"></i>
                </button>
              </div>
              <div class="form-text text-danger d-none" id="errorPass">La contraseña debe tener al menos 6 caracteres.
              </div>
            </div>

            <!-- Botón enviar -->
            <div class="d-grid mt-3">
              <button type="submit" class="btn btn-primary btn-submit">Acceder</button>
            </div>
          </form>

          
        </div>
      </div>

    </div>
  </div>



  <!-- Bootstrap JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Validación Bootstrap
    (function () {
      'use strict';
      const form = document.getElementById('formLogin');
      form.addEventListener('submit', function (event) {
        if (!form.checkValidity()) {
          event.preventDefault();
          event.stopPropagation();
        }
        form.classList.add('was-validated');
      }, false);
    })();

    // Mostrar/ocultar contraseña
    function togglePassword() {
      const passwordInput = document.getElementById("clave");
      const toggleIcon = document.getElementById("toggleIcon");

      if (passwordInput.type === "password") {
        passwordInput.type = "text";
        toggleIcon.classList.remove("bi-eye-slash");
        toggleIcon.classList.add("bi-eye");
      } else {
        passwordInput.type = "password";
        toggleIcon.classList.remove("bi-eye");
        toggleIcon.classList.add("bi-eye-slash");
      }
    }
  </script>

</body>

</html>