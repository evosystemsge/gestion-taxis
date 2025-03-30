<?php
session_start();
include 'config.php';

// Verificar si el usuario está autenticado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Obtener todos los ingresos
$query_ingresos = "SELECT i.monto, i.concepto, v.placa, c.nombre AS conductor, i.deuda_actual FROM ingresos i
                  LEFT JOIN vehiculos v ON i.vehiculo_id = v.id
                  LEFT JOIN conductores c ON v.conductor_id = c.id";
$result_ingresos = mysqli_query($conn, $query_ingresos);

// Calcular el total de ingresos
$query_total_ingresos = "SELECT SUM(monto) AS total_ingresos FROM ingresos";
$result_total_ingresos = mysqli_query($conn, $query_total_ingresos);
$total_ingresos = mysqli_fetch_assoc($result_total_ingresos)['total_ingresos'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Todos los Ingresos</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    
    <link rel="stylesheet" href="menu.css">
    <style>
        .table th, .table td {
            text-align: center;
        }

        .table thead {
            background-color: #28a745;
            color: white;
        }

        .table tbody tr:hover {
            background-color: #f1f1f1;
        }

        .table .sin-deuda {
            background-color: #d4edda;
        }
    </style>
</head>
<body class="bg-light">
    <?php include('menu.php'); ?>
    <div class="container mt-5">
        <div class="card shadow-lg">
            <div class="card-header bg-success text-white text-center">
                <h3><i class="fa-solid fa-money-bill-wave"></i> Todos los Ingresos</h3>
            </div>
            <div class="card-body">
                <h4>Total Ingresos: <?php echo number_format($total_ingresos, 0); ?> XAF</h4>
                <table class="table table-bordered mt-3">
                    <thead>
                        <tr>
                            <th>Vehículo</th>
                            <th>Conductor</th>
                            <th>Concepto</th>
                            <th>Monto</th>
                            <th>Deuda</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = mysqli_fetch_assoc($result_ingresos)) : ?>
                            <tr class="<?php echo ($row['deuda_actual'] == 0) ? 'sin-deuda' : ''; ?>">
                                <td><?php echo $row['placa']; ?></td>
                                <td><?php echo $row['conductor']; ?></td>
                                <td><?php echo $row['concepto']; ?></td>
                                <td><?php echo number_format($row['monto'], 0); ?> XAF</td>
                                <td><?php echo ($row['deuda_actual'] == 0) ? 'Sin deudas' : number_format($row['deuda_actual'], 0) . ' XAF'; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>
