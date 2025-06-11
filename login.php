

<div class="container-fluid d-flex flex-column flex-md-row align-items-center justify-content-center p-0 login-wrapper">
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

        <form id="formLogin" method="POST" action="api/login.php" novalidate>


          <!-- Campo: Correo -->
          <div class="mb-4">
            <label for="usuario" class="form-label-icon">
              <i class="bi bi-envelope-at text-success"></i> usuario
            </label>
            <div class="input-group">
              <span class="input-group-text bg-white">
                <i class="bi bi-person text-success"></i>
              </span>
              <input type="text" id="usuario" name="usuario" class="form-control" placeholder="correo@ejemplo.com"
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