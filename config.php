<?php
$host = 'localhost';
$user = 'u714680163_evo_ps_tx';
$password = 'TaxisGsT@202X';
$dbname = 'u714680163_gestion_taxi_f';

$conn = mysqli_connect($host, $user, $password, $dbname);

if (!$conn) {
    die("Error de conexión: " . mysqli_connect_error());
}
?>