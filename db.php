<?php
$host = 'localhost'; // Altere se necessário
$user = 'u546512033_root'; // Seu usuário MySQL
$pass = '3232.AmInfo'; // Sua senha MySQL
$dbname = 'u546512033_siscontro';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}
?>