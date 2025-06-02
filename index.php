<?php
if (session_status() == PHP_SESSION_NONE) {
  // Si la sesión no está iniciada, se inicia
  session_start();
}

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Iniciar Sesión | Consultorio Dr. Oscar</title>

    <style>
        /* Reset */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html,
        body {
            height: 100%;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
        }

        .container {
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .login-wrapper {
            display: flex;
            flex: 1;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 1rem;
        }

        .login-card {
            display: flex;
            flex-direction: column;
            background-color: #ffffff;
            border-radius: 1rem;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            max-width: 900px;
            width: 100%;
        }

        .image-side {
            display: none;
            background: url('img/img-1.jpg') no-repeat center center;
            background-size: cover;
        }

        .form-side {
            padding: 2rem;
            flex: 1;
        }

        .circle-figure {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: linear-gradient(135deg, #198754, #0d6efd);
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
            font-size: 2.5rem;
            color: white;
            display: flex;
            justify-content: center;
            align-items: center;
            margin: 0 auto 1.5rem auto;
        }

        h3 {
            text-align: center;
            color: #198754;
            font-weight: bold;
            margin-bottom: 1.5rem;
        }

        h3 span {
            color: #0d6efd;
        }

        .form-group {
            margin-bottom: 1.2rem;
        }

        label {
            font-weight: 600;
            margin-bottom: 0.3rem;
            display: block;
            color: #495057;
        }

        input {
            width: 100%;
            padding: 0.6rem;
            border: 1px solid #ced4da;
            border-radius: 0.5rem;
            font-size: 1rem;
        }

        input:focus {
            outline: none;
            border-color: #80bdff;
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.15);
        }

        .toggle-password {
            margin-top: 0.3rem;
            font-size: 0.9rem;
            color: #0d6efd;
            cursor: pointer;
            text-align: right;
        }

        .btn-submit {
            width: 100%;
            background-color: #0d6efd;
            color: white;
            border: none;
            padding: 0.8rem;
            border-radius: 2rem;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            margin-top: 1rem;
        }

        .btn-submit:hover {
            background-color: #0b5ed7;
        }

        .error {
            color: red;
            font-size: 0.85rem;
            display: none;
        }

        /* Responsive */
        @media (min-width: 768px) {
            .login-card {
                flex-direction: row;
            }

            .image-side {
                display: block;
                width: 50%;
                height: auto;
            }

            .form-side {
                width: 50%;
            }

            .circle-figure {
                display: flex;
            }
        }

        /* -------- cargando------ */
        .spinner {
            margin: 10px auto;
            width: 30px;
            height: 30px;
            border: 4px solid #ccc;
            border-top: 4px solid #007bff;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <!-- para mostrar mensajes flotantes -->
        <div id="toast-container" class="position-fixed top-0 start-50 translate-middle-x p-3"
            style="z-index: 1060; max-width: 90%; width: 400px;"></div>
        <!-- carga visual de espera -->
        <div id="loading" style="display:none; text-align:center; margin-top:15px;">
            <span>Cargando, por favor espera...</span>
            <div class="spinner"></div>
        </div>

        <div class="login-wrapper">
            <div class="login-card">
                <div class="image-side"></div>

                <div class="form-side">
                    <div class="circle-figure">❤</div>

                    <h3>
                        Bienvenido al <span>Consultorio Dr. Oscar</span>
                    </h3>

                    <form id="formLogin" novalidate>
                        <div class="form-group">
                            <label for="usuario">Nombre de Usuario </label>
                            <input type="text" id="usuario" name="usuario" placeholder="Ej: admin97" required>
                            <div class="error" id="errorUsuario">Ingrese un usuario válido.</div>
                        </div>

                        <div class="form-group">
                            <label for="contrasena">Contraseña</label>
                            <input type="password" id="contrasena" name="contrasena" placeholder="••••••••"
                                minlength="6" required>
                            <div class="toggle-password" onclick="togglePassword()">Mostrar / Ocultar contraseña</div>
                            <div class="error" id="errorPass">La contraseña debe tener al menos 6 caracteres.</div>
                        </div>

                        <button type="submit" class="btn-submit">Acceder</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script src="js/alerta.js"></script>
    <script>
        document.getElementById("formLogin").addEventListener("submit", async function (e) {
            e.preventDefault(); // evitar envío por defecto

            const form = this; // referencia al formulario
            const usuarioInput = document.getElementById("usuario");
            const passInput = document.getElementById("contrasena");

            const usuario = usuarioInput.value.trim();
            const contrasena = passInput.value.trim();

            let valid = true;

            // Validación de correo básico
            if (!usuario.length < 3) {
                document.getElementById("errorUsuario").style.display = "block";
                valid = false;
            } else {
                document.getElementById("errorUsuario").style.display = "none";
            }

            // Validación de longitud de contraseña
            if (contrasena.length < 5) {
                document.getElementById("errorPass").style.display = "block";
                valid = false;
            } else {
                document.getElementById("errorPass").style.display = "none";
            }

            if (!valid) return; // si no es válido, no continúa

            // Enviar datos usando FormData
            const formData = new FormData();
            formData.append('correo', usuario);
            formData.append('contrasena', contrasena);

            try {
                const res = await fetch('api/login.php', {
                    method: 'POST',
                    body: formData
                });

                const data = await res.json();

                if (res.status) {
                    let countdown = 4;
                    mostrarToast('success', `Bienvenido, redirigiendo en ${countdown} segundos...`);
                    ocument.getElementById("loading").style.display = "block";
                    const interval = setInterval(() => {
                        countdown--;
                        mostrarToast('success', `Redirigiendo en ${countdown} segundos...`);
                        if (countdown <= 0) {
                            clearInterval(interval);
                            window.location.href = 'admin/index.php';
                        }
                    }, 1000);

                } else {
                    ocument.getElementById("loading").style.display = "block";
                    mostrarToast('warning', data.message || 'Error de autenticación');
                }

            } catch (err) {
                console.error('Error al conectar con el servidor:', err);
            }
        });

        // Mostrar/ocultar contraseña
        function togglePassword() {
            const input = document.getElementById("contrasena");
            input.type = input.type === "password" ? "text" : "password";
        }
    </script>

</body>

</html>