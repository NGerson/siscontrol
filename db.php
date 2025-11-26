<?php
$host = 'localhost'; // Altere se necessário
$user = 'root'; // Seu usuário MySQL
$pass = ''; // Sua senha MySQL
$dbname = 'controle_alimentacao';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Erro de conexão: " . $conn->connect_error);
}
?>