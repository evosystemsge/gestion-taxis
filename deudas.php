<?php
session_start();
include 'config.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Procesar actualización de deuda
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ingreso_id = $_POST['ingreso_id'];
    $monto_a_pagar = intval($_POST['monto_a_pagar']); // Monto que se va a pagar

    // Obtener el ingreso correspondiente
    $query = "SELECT monto, deuda_actual FROM ingresos WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "i", $ingreso_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $ingreso = mysqli_fetch_assoc($result);

    // Validar que el monto a pagar no sea mayor que la deuda ni menor que 0
    if ($monto_a_pagar <= 0) {
        $error = "El monto a pagar debe ser mayor que 0.";
    } elseif ($monto_a_pagar > $ingreso['deuda_actual']) {
        $error = "El monto a pagar no puede ser mayor que la deuda actual.";
    } else {
        // Obtener el saldo de la caja GENERAL
        $query_caja = "SELECT saldo FROM cajas WHERE nombre = 'GENERAL'";
        $result_caja = mysqli_query($conn, $query_caja);
        $caja = mysqli_fetch_assoc($result_caja);

        // Calcular el nuevo saldo de caja
        $nuevo_saldo_caja = $caja['saldo'] + $monto_a_pagar;

        // Calcular la nueva deuda
        $nueva_deuda = $ingreso['deuda_actual'] - $monto_a_pagar;
        if ($nueva_deuda < 0) {
            $nueva_deuda = 0; // La deuda no puede ser negativa
        }

        // Actualizar el monto del ingreso (sumar el monto pagado)
        $nuevo_monto_ingreso = $ingreso['monto'] + $monto_a_pagar;

        // Actualizar la deuda y el monto del ingreso
        $query_update = "UPDATE ingresos SET monto = ?, deuda_actual = ? WHERE id = ?";
        $stmt_update = mysqli_prepare($conn, $query_update);
        mysqli_stmt_bind_param($stmt_update, "iii", $nuevo_monto_ingreso, $nueva_deuda, $ingreso_id);
        mysqli_stmt_execute($stmt_update);

        // Actualizar el saldo de la caja
        $query_update_caja = "UPDATE cajas SET saldo = ? WHERE nombre = 'GENERAL'";
        $stmt_update_caja = mysqli_prepare($conn, $query_update_caja);
        mysqli_stmt_bind_param($stmt_update_caja, "i", $nuevo_saldo_caja);
        mysqli_stmt_execute($stmt_update_caja);

        echo "<script>
            alert('Deuda y monto actualizado correctamente');
            window.location = 'deudas.php';
        </script>";
    }
}

// Obtener ingresos con deuda mayor a 0
$query = "SELECT * FROM ingresos WHERE deuda_actual > 0";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Deudas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> <!-- Asegúrate de incluir esto -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="menu.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>

<body class="bg-light">
    <?php include('menu.php'); ?>
    <div class="container mt-5">
        <div class="card shadow-lg">
            <div class="card-header bg-warning text-black text-center">
                <h3><i class="fa-solid fa-file-invoice-dollar"></i> Gestionar Deudas</h3>
            </div>
            <div class="card-body">
                <?php if (isset($error)) : ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Vehículo</th>
                            <th>Monto</th>
                            <th>Deuda</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result)) : ?>
                            <tr>
                                <td><?php echo $row['id']; ?></td>
                                <td><?php echo $row['vehiculo_id']; ?></td>
                                <td><?php echo number_format($row['monto'], 0); ?> XAF</td>
                                <td><?php echo number_format($row['deuda_actual'], 0); ?> XAF</td>
                                <td>
                                    <!-- Formulario para actualizar deuda -->
                                    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#updateDeudaModal" onclick="setIngresoData(<?php echo $row['id']; ?>, <?php echo $row['monto']; ?>, <?php echo $row['deuda_actual']; ?>)">Actualizar</button>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal para actualizar deuda -->
    <div class="modal fade" id="updateDeudaModal" tabindex="-1" aria-labelledby="updateDeudaModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="updateDeudaModalLabel">Actualizar Deuda</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form method="POST" onsubmit="return validateMontoAPagar()">
                        <input type="hidden" name="ingreso_id" id="ingreso_id">
                        <div class="mb-3">
                            <label class="form-label">Deuda Actual</label>
                            <input type="text" id="deuda_actual" class="form-control" readonly>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Monto a Pagar</label>
                            <input type="number" name="monto_a_pagar" id="monto_a_pagar" class="form-control" min="0" required>
                        </div>
                        <button type="submit" class="btn btn-success w-100">Actualizar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Función para cargar los datos del ingreso en el modal
        function setIngresoData(ingreso_id, monto, deuda) {
            document.getElementById('ingreso_id').value = ingreso_id;
            document.getElementById('deuda_actual').value = deuda;
            document.getElementById('monto_a_pagar').value = 0; // Iniciar con 0
        }

        // Validar el monto a pagar antes de enviar el formulario
        function validateMontoAPagar() {
            var monto_a_pagar = parseInt(document.getElementById('monto_a_pagar').value);
            var deuda_actual = parseInt(document.getElementById('deuda_actual').value);

            // Verificar que el monto a pagar no sea mayor que la deuda ni menor que 0
            if (isNaN(monto_a_pagar) || monto_a_pagar <= 0) {
                alert("El monto a pagar debe ser mayor que 0.");
                return false;
            }
            if (monto_a_pagar > deuda_actual) {
                alert("El monto a pagar no puede ser mayor que la deuda actual.");
                return false;
            }
            return true;
        }
    </script>
</body>

</html>
