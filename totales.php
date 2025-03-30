<?php


// Obtener el total de ingresos
$query_ingresos = "SELECT SUM(monto) AS total_ingresos FROM ingresos";
$result_ingresos = mysqli_query($conn, $query_ingresos);
$row_ingresos = mysqli_fetch_assoc($result_ingresos);
$total_ingresos = $row_ingresos['total_ingresos'] ? $row_ingresos['total_ingresos'] : 0;

// Obtener el total de gastos
$query_gastos = "SELECT SUM(monto) AS total_gastos FROM gastos";
$result_gastos = mysqli_query($conn, $query_gastos);
$row_gastos = mysqli_fetch_assoc($result_gastos);
$total_gastos = $row_gastos['total_gastos'] ? $row_gastos['total_gastos'] : 0;

// Calcular el beneficio neto
$beneficio = $total_ingresos - $total_gastos;

// Obtener el saldo de la caja 'GENERAL'
$query_caja = "SELECT saldo FROM cajas WHERE nombre = 'GENERAL'";
$result_caja = mysqli_query($conn, $query_caja);
$row_caja = mysqli_fetch_assoc($result_caja);
$saldo_caja = $row_caja['saldo'] ? $row_caja['saldo'] : 0;

?>

<div class="container">
    <div class="box-container">
        <!-- Redirigir a la página de ingresos al hacer clic -->
        <a href="ver_ingresos.php" class="box box-ingreso no-underline">
            <h4>Total Ingresos:</h4>
            <p><?php echo number_format($total_ingresos, 0); ?> XAF</p>
        </a>

        <!-- Redirigir a la página de gastos al hacer clic -->
        <a href="ver_gastos.php" class="box box-gasto no-underline">
            <h4>Total Gastos:</h4>
            <p><?php echo number_format($total_gastos, 0); ?> XAF</p>
        </a>

        <div class="box box-beneficio">
            <h4>Beneficio Neto:</h4>
            <p><?php echo number_format($beneficio, 0); ?> XAF</p>
        </div>

        <div class="box box-caja">
            <h4>Caja:</h4>
            <p><?php echo number_format($saldo_caja, 0); ?> XAF</p>
        </div>
    </div>
</div>

