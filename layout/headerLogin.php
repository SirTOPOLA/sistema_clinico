

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
      background: url('img/img-login.jpg') no-repeat center center;
      background-size: contain;
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
