<?php
include 'db.php';
session_start();
// Opcional: Adicionar verificação de autenticação mais rigorosa aqui se necessário
// if (!isset($_SESSION['user_id'])) { http_response_code(401); exit(); }

header('Content-Type: application/json');

if (isset($_GET['item_id']) && isset($_GET['unit_name'])) {
    $item_id = intval($_GET['item_id']);
    $unit_name = $_GET['unit_name'];
    
    // Busca os 50 registros mais recentes para o item e unidade
    $stmt = $conn->prepare("SELECT date, type, quantity FROM inventory WHERE item_id = ? AND unit_name = ? ORDER BY date DESC LIMIT 50");
    $stmt->bind_param("is", $item_id, $unit_name);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $history = [];
    while ($row = $result->fetch_assoc()) {
        $history[] = $row;
    }
    
    echo json_encode($history);
    
} else {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Parâmetros item_id e unit_name são obrigatórios.']);
}
?>