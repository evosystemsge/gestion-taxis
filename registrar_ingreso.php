<?php
session_start();
include 'config.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $vehiculo = $_POST['vehiculo'];
    $monto = intval($_POST['monto']); // Convertir a entero
    $concepto = $_POST['concepto'];
    $monto_diario = intval($_POST['monto_diario']); // Convertir a entero
    $deuda_actual = $monto_diario - $monto;

    // Validar que los campos no estén vacíos
    if (empty($vehiculo) || empty($monto) || empty($concepto)) {
        $error = "Todos los campos son obligatorios.";
    } elseif ($monto < 0) {
        // Validar que el monto no sea menor que 0
        $error = "El monto no puede ser menor que 0.";
    } elseif ($monto > $monto_diario) {
        // Validar que el monto no sea mayor que el monto diario
        $error = "El monto no puede ser mayor que el monto diario.";
    } else {
        // Insertar en la base de datos
        $query = "INSERT INTO ingresos (vehiculo_id, monto, concepto, monto_diario, deuda_actual) VALUES (?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);
        mysqli_stmt_bind_param($stmt, "iisii", $vehiculo, $monto, $concepto, $monto_diario, $deuda_actual);

        if (mysqli_stmt_execute($stmt)) {
            // Actualizar saldo de la caja GENERAL
            $query_caja = "UPDATE cajas SET saldo = saldo + ? WHERE nombre = 'GENERAL'";
            $stmt_caja = mysqli_prepare($conn, $query_caja);
            mysqli_stmt_bind_param($stmt_caja, "i", $monto);
            mysqli_stmt_execute($stmt_caja);

            echo "<script>
                alert('Ingreso registrado.');
            </script>";
        } else {
            $error = "Error al registrar el ingreso.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Ingreso</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="menu.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script>
        // Función para calcular la deuda actual en tiempo real
        function calcularDeuda() {
            let montoDiario = parseInt(document.getElementById('monto_diario').value) || 0;
            let monto = parseInt(document.getElementById('monto').value) || 0;

            // Calcular la deuda actual
            let deudaActual = montoDiario - monto;

            // Mostrar la deuda actual en el campo correspondiente
            document.getElementById('deuda_actual').textContent = "Deuda Actual: XAF " + deudaActual.toLocaleString();

            // Validar que el monto ingresado no sea mayor que el monto diario y no sea menor que 0
            if (monto > montoDiario) {
                document.getElementById('monto').setCustomValidity('El monto no puede ser mayor que el monto diario');
            } else if (monto < 0) {
                document.getElementById('monto').setCustomValidity('El monto no puede ser menor que 0');
            } else {
                document.getElementById('monto').setCustomValidity('');
            }
        }
    </script>
</head>

<body class="bg-light">
    <?php include('menu.php'); ?>
    <div class="container mt-5">
        <div class="card shadow-lg">
            <div class="card-header bg-success text-white text-center">
                <h3><i class="fa-solid fa-money-bill-wave"></i> Registrar Ingreso</h3>
            </div>
            <div class="card-body">
                <?php if (isset($error)) : ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label"><i class="fa-solid fa-car"></i> Vehículo</label>
                        <select name="vehiculo" class="form-control" required>
                            <option value="">Seleccione un vehículo</option>
                            <?php
                            // Obtener los vehículos disponibles con su conductor asociado
                            $result = mysqli_query($conn, "SELECT v.id, v.placa, c.nombre AS conductor FROM vehiculos v LEFT JOIN conductores c ON v.conductor_id = c.id WHERE v.conductor_id IS NOT NULL");
                            while ($row = mysqli_fetch_assoc($result)) :
                            ?>
                                <option value="<?php echo $row['id']; ?>">
                                    <?php echo $row['placa']; ?> - <?php echo $row['conductor']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fa-solid fa-calendar-day"></i> Monto Diario</label>
                        <input type="number" name="monto_diario" id="monto_diario" class="form-control" min="0" required oninput="calcularDeuda()">
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fa-solid fa-dollar-sign"></i> Monto</label>
                        <input type="number" name="monto" id="monto" class="form-control" min="0" required oninput="calcularDeuda()">
                    </div>
                    
                    <!-- Mostrar la deuda actual -->
                    <div class="mb-3" id="deuda_actual" style="color: red; font-weight: bold;">
                        Deuda Actual: XAF 0
                    </div>
                    <div class="mb-3">
                        <label class="form-label"><i class="fa-solid fa-file-alt"></i> Concepto</label>
                        <textarea name="concepto" class="form-control" rows="3" placeholder="Descripción del ingreso" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-success w-100"><i class="fa-solid fa-save"></i> Guardar Ingreso</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
