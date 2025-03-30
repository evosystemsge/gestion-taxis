<?php
session_start();
include 'config.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Obtener todos los gastos
$query_gastos = "SELECT g.monto, g.concepto, v.placa, c.nombre AS conductor FROM gastos g
                LEFT JOIN vehiculos v ON g.vehiculo_id = v.id
                LEFT JOIN conductores c ON v.conductor_id = c.id";
$result_gastos = mysqli_query($conn, $query_gastos);

// Calcular el total de gastos
$query_total_gastos = "SELECT SUM(monto) AS total_gastos FROM gastos";
$result_total_gastos = mysqli_query($conn, $query_total_gastos);
$total_gastos = mysqli_fetch_assoc($result_total_gastos)['total_gastos'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todos los Gastos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
     <link rel="stylesheet" href="menu.css">
</head>
<body class="bg-light">
    <?php include('menu.php'); ?>
    <div class="container mt-5">
        <div class="card shadow-lg">
            <div class="card-header bg-danger text-white text-center">
                <h3><i class="fa-solid fa-money-bill-wave"></i> Todos los Gastos</h3>
            </div>
            <div class="card-body">
                <h4>Total Gastos: <?php echo number_format($total_gastos, 0); ?> XAF</h4>
                <table class="table table-bordered mt-3 bg-danger text-white text-center">
                    <thead>
                        <tr>
                            <th>Vehículo</th>
                            <th>Conductor</th>
                            <th>Concepto</th>
                            <th>Monto</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result_gastos)) : ?>
                            <tr>
                                <td><?php echo $row['placa']; ?></td>
                                <td><?php echo $row['conductor']; ?></td>
                                <td><?php echo $row['concepto']; ?></td>
                                <td><?php echo number_format($row['monto'], 0); ?> XAF</td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
