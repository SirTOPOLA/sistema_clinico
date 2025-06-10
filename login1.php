<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card p-4">
                <div class="text-center">
                    <img src="img/logo.jpeg" alt="Logo hospital" class="hospital-logo">
                    <h4 class="card-title mb-3">Hospital Regional de Sampaka</h4>
                    <p class="text-muted"><strong>Acceso al sistema</strong></p>
                </div>
                <form id="loginForm" method="POST" action="api/login.php">

                    <div class="mb-3">
                        <label for="username" class="form-label text-success "><strong>Usuario</strong></label>
                        <div class="input-group">
                            <span class="input-group-text bg-white "">
                             <i class=" bi bi-person"></i>
                            </span>
                            <input type="text" class="form-control" name="usuario" id="username"
                                placeholder="Ingrese su usuario" required>
                        </div>
                        <!-- Después del campo usuario -->
                        <div id="userError" class="text-danger small mt-1"></div>
                    </div>
                    <div class="mb-3 position-relative">
                        <label for="password" class="form-label text-success "><strong>Contraseña</strong></label>
                        <div class="input-group">
                            <span class="input-group-text bg-white " ">
                                 <i class=" bi bi-lock"></i>
                            </span>
                            <input type="password" class="form-control" id="password"
                                placeholder="Ingrese su contraseña" name="contrasena" required>
                            <span class="input-group-text bg-white toggle-password" onclick="togglePassword()">
                                <i class="bi bi-eye" id="toggleIcon"></i>
                            </span>
                        </div>
                        <!-- Después del campo contraseña -->
                        <div id="passError" class="text-danger small mt-1"></div>

                    </div>
                    <div class="d-grid">
                        <button type="submit" class="btn btn-login"><i class="bi bi-save"></i> Ingresar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>