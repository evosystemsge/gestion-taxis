<?php
session_start();
include 'config.php';

// Redirigir si no está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Obtener totales de ingresos, gastos y beneficios
$query = "SELECT 
            (SELECT SUM(monto) FROM ingresos) AS total_ingresos,
            (SELECT SUM(monto) FROM gastos) AS total_gastos";
$result = mysqli_query($conn, $query);
$row = mysqli_fetch_assoc($result);
$total_ingresos = $row['total_ingresos'] ?: 0;
$total_gastos = $row['total_gastos'] ?: 0;
$beneficio = $total_ingresos - $total_gastos;

// Consultar todos los registros de ingresos y gastos combinados, ordenados por fecha descendente
$query_registros = "
    (SELECT i.id, i.vehiculo_id AS vehiculo, i.monto, i.concepto, i.fecha, 'ingreso' AS tipo 
     FROM ingresos i)
    UNION
    (SELECT g.id, g.vehiculo_id AS vehiculo, g.monto, g.concepto, g.fecha, 'gasto' AS tipo 
     FROM gastos g)
    ORDER BY fecha DESC
";
$result_registros = mysqli_query($conn, $query_registros);

?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Finanzas</title>
    <link rel="stylesheet" href="totales.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body {
            background-color: #f4f4f9;
            color: #333;
        }

        .navbar {
            background-color: #0056b3;
            padding: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .navbar a {
            color: white;
            padding: 10px 20px;
            text-decoration: none;
            font-size: 18px;
        }

        .navbar a:hover {
            background-color: #004085;
            border-radius: 5px;
        }

        .container {
            margin-top: 50px;
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            width: 90%;
            max-width: 1200px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .box {
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            color: white;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .box-ingreso {
            background-color: #28a745;
        }

        .box-gasto {
            background-color: #dc3545;
        }

        .box-beneficio {
            background-color: rgb(46, 67, 89);
        }

        .btn-logout {
            background-color: #6c757d;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            font-size: 16px;
        }

        .btn-logout:hover {
            background-color: #5a6268;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .btn-ingreso {
            background-color: #28a745;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
        }

        .btn-ingreso:hover {
            background-color: #218838;
        }

        .btn-gasto {
            background-color: #dc3545;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
        }

        .btn-gasto:hover {
            background-color: #c82333;
        }

        table {
            width: 100%;
            margin-top: 30px;
            border-collapse: collapse;
        }

        table,
        th,
        td {
            border: 1px solid #ddd;
        }

        th,
        td {
            padding: 12px;
            text-align: center;
        }

        th {
            background-color: #007bff;
            color: white;
        }

        .form-inline {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .form-inline .form-group {
            margin-right: 15px;
        }

        .box-container {
            display: flex;
            justify-content: space-between;
            gap: 15px;
        }
    </style>
</head>

<body>
    <?php include('menu.php'); ?>
    <?php include('totales.php'); ?>
    

    <div class="container">
        <h3 class="mt-4">Transacciones</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Vehículo</th>
                    <th>Conductor</th>
                    <th>Tipo</th>
                    <th>Monto</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php
                while ($row = mysqli_fetch_assoc($result_registros)) {
                    // Obtener datos del vehículo
                    $vehiculo_id = $row['vehiculo'];

                    // Consultar la placa del vehículo
                    $vehiculo_query = "SELECT placa FROM vehiculos WHERE id = $vehiculo_id";
                    $vehiculo_result = mysqli_query($conn, $vehiculo_query);

                    if ($vehiculo_result && mysqli_num_rows($vehiculo_result) > 0) {
                        $vehiculo = mysqli_fetch_assoc($vehiculo_result)['placa'];
                    } else {
                        $vehiculo = '-'; // Valor por defecto si no se encuentra el vehículo
                    }

                    // Consultar el nombre del conductor
                    $conductor_query = "SELECT c.nombre FROM conductores c
                        JOIN vehiculos v ON c.id = v.conductor_id
                        WHERE v.id = $vehiculo_id";
                    $conductor_result = mysqli_query($conn, $conductor_query);

                    if ($conductor_result && mysqli_num_rows($conductor_result) > 0) {
                        $conductor = mysqli_fetch_assoc($conductor_result)['nombre'];
                    } else {
                        $conductor = '-'; // Valor por defecto si no se encuentra el conductor
                    }
                ?>

                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo $vehiculo; ?></td>
                        <td><?php echo $conductor; ?></td>
                        <td <?php echo ($row['tipo'] == 'ingreso') ? "class='bg-success text-white'" : "class='bg-danger text-white'"; ?>>
                            <?php echo ucfirst($row['tipo']); ?>
                        </td>

                        <td><?php echo number_format($row['monto'], 0); ?> XAF</td>
                        <td><?php echo $row['fecha']; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

    </div>
</body>

</html>
