<?php
$host = 'localhost'; // Altere se necessário
$user = 'u546512033_siscontrol'; // Seu usuário MySQL
$pass = '3232.AmInfo'; // Sua senha MySQL
$dbname = 'u546512033_siscontrol';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}
?>