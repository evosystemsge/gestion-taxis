<?php
session_start();
include 'config.php';

try {
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        $query = "SELECT * FROM usuarios WHERE username = ? AND password = ?";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "ss", $username, $password);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) > 0) {
            $user = mysqli_fetch_assoc($result);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role'] = $user['role'];
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({
                        title: 'Acceso permitido', 
                        text: 'Redirigiendo...', 
                        icon: 'success', 
                        timer: 2000, 
                        showConfirmButton: false
                    }).then(() => { window.location='index.php'; });
                });
            </script>";
        } else {
            echo "<script>
                document.addEventListener('DOMContentLoaded', function() {
                    Swal.fire({title: 'Error', text: 'Usuario o contraseña incorrectos', icon: 'error'});
                });
            </script>";
        }
    }
} catch (Exception $e) {
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({title: 'Error', text: '" . addslashes($e->getMessage()) . "', icon: 'error'});
        });
    </script>";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Gestión de Taxis</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            background: linear-gradient(to right, #004085, #007bff);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .login-container {
            max-width: 400px;
            padding: 30px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
            text-align: center;
        }
        .btn-blue {
            background-color: #007bff;
            border: none;
        }
        .btn-blue:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2 class="mb-3"><i class="fa-solid fa-taxi text-primary"></i> Bienvenido</h2>
        <p>Inicia sesión para continuar</p>
        <form method="POST">
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-user"></i></span>
                    <input type="text" class="form-control" name="username" placeholder="Usuario" required>
                </div>
            </div>
            <div class="mb-3">
                <div class="input-group">
                    <span class="input-group-text"><i class="fa-solid fa-lock"></i></span>
                    <input type="password" class="form-control" name="password" placeholder="Contraseña" required>
                </div>
            </div>
            <button type="submit" class="btn btn-blue w-100 text-white"><i class="fa-solid fa-sign-in-alt"></i> Iniciar sesión</button>
        </form>
        <p class="mt-3 text-muted" style="cursor: pointer;" onclick="forgotPassword()"><i class="fa-solid fa-key"></i> ¿Olvidaste tu contraseña?</p>
    </div>
    
    <script>
        function forgotPassword() {
            Swal.fire({
                title: 'Recuperar contraseña',
                text: 'Por favor, contacta al administrador.',
                icon: 'info',
                confirmButtonText: 'OK'
            });
        }
    </script>
</body>
</html>
