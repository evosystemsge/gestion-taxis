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

    // Validar que los campos no estén vacíos
    if (empty($monto) || empty($concepto)) {
        $error = "Todos los campos son obligatorios.";
    } else {
        // Obtener el saldo actual de la caja 'GENERAL'
        $query_caja = "SELECT saldo FROM cajas WHERE nombre = 'GENERAL'";
        $result_caja = mysqli_query($conn, $query_caja);
        $row_caja = mysqli_fetch_assoc($result_caja);

        // Verificar si el monto del gasto es mayor que el saldo disponible en la caja
        if ($monto > $row_caja['saldo']) {
            $error = "El monto del gasto no puede ser mayor que el saldo disponible en la caja.";
        } else {
            // Insertar en la base de datos
            $query = "INSERT INTO gastos (vehiculo_id, monto, concepto) VALUES (?, ?, ?)";
            $stmt = mysqli_prepare($conn, $query);
            mysqli_stmt_bind_param($stmt, "iis", $vehiculo, $monto, $concepto);

            if (mysqli_stmt_execute($stmt)) {
                // Actualizar saldo de la caja GENERAL
                $query_caja_update = "UPDATE cajas SET saldo = saldo - ? WHERE nombre = 'GENERAL'";
                $stmt_caja_update = mysqli_prepare($conn, $query_caja_update);
                mysqli_stmt_bind_param($stmt_caja_update, "i", $monto);
                mysqli_stmt_execute($stmt_caja_update);

                echo "<script>
                    alert('Gasto registrado.');
                </script>";
            } else {
                $error = "Error al registrar el gasto.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Gasto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="menu.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>

<body class="bg-light">
    <?php include('menu.php'); ?>
    <div class="container mt-5">
        <div class="card shadow-lg">
            <div class="card-header bg-danger text-white text-center">
                <h3><i class="fa-solid fa-money-bill-wave"></i> Registrar Gasto</h3>
            </div>
            <div class="card-body">
                <?php if (isset($error)) : ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label"><i class="fa-solid fa-car"></i> Vehículo</label>
                        <select name="vehiculo" class="form-control">
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
                        <label class="form-label"><i class="fa-solid fa-dollar-sign"></i> Monto</label>
                        <input type="number" name="monto" class="form-control" min="0" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><i class="fa-solid fa-file-alt"></i> Concepto</label>
                        <textarea name="concepto" class="form-control" rows="3" placeholder="Descripción del gasto" required></textarea>
                    </div>

                    <button type="submit" class="btn btn-danger w-100"><i class="fa-solid fa-save"></i> Guardar Gasto</button>
                </form>
            </div>
        </div>
    </div>
</body>

</html>
