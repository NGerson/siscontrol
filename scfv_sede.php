<?php
include 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) header("Location: login.php");

// 1. DEFINIÇÃO DA UNIDADE
$unit_name = "SCFV SEDE"; 
$unit_file = "scfv_sede.php"; // Nome do arquivo atual para redirecionamento

// Funções para estoque
function getStock($item_id, $unit_name) {
    global $conn;
    $stmt = $conn->prepare("SELECT SUM(CASE WHEN type='entrada' THEN quantity ELSE -quantity END) AS stock FROM inventory WHERE item_id = ? AND unit_name = ?");
    $stmt->bind_param("is", $item_id, $unit_name);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['stock'] ?? 0;
}

// Funções para histórico (últimos 10 registros)
function getHistory($item_id, $unit_name) {
    global $conn;
    $stmt = $conn->prepare("SELECT date, type, quantity FROM inventory WHERE item_id = ? AND unit_name = ? ORDER BY date DESC LIMIT 10");
    $stmt->bind_param("is", $item_id, $unit_name);
    $stmt->execute();
    return $stmt->get_result();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $item_id = $_POST['item_id'];
    $quantity = $_POST['quantity'];
    $date = $_POST['date'];
    $type = $_POST['type'];
    
    // Validação para Saída
    if ($type == 'saida') {
        $current_stock = getStock($item_id, $unit_name);
        if ($quantity > $current_stock) {
            $error_message = "Erro: Quantidade de saída ({$quantity}) maior que o estoque atual ({$current_stock}) em {$unit_name}.";
        }
    }
    
    if (!isset($error_message)) {
        // NOVO INSERT: Incluindo unit_name
        $stmt = $conn->prepare("INSERT INTO inventory (item_id, quantity, date, type, unit_name) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iiss", $item_id, $quantity, $date, $type, $unit_name);
        
        $stmt->execute();
        header("Location: {$unit_file}");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Controle de Estoque - <?php echo $unit_name; ?></title>
    <link rel="icon" type="image/png" href="img/control2.1.png"> 
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/style.css" rel="stylesheet"> 
    
    <style>
        body {
            /* ATENÇÃO: Mude este caminho para o local real da sua imagem */
            background-image: url('img/background.jpg'); 
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-5">
        <div class="container">
            <a class="navbar-brand" href="units.php">
                <img src="img/control2.1.png" alt="Logo S Control" style="height: 30px; margin-right: 8px; border-radius: 5px;">
                Controle de Unidades | <?php echo $unit_name; ?>
            </a>
            <div class="d-flex">
                <?php if ($_SESSION['role'] == 'admin'): ?>
                    <a href="dashboard.php" class="btn btn-outline-light me-2">Dashboard Admin</a>
                <?php endif; ?>
                <a href="logout.php" class="btn btn-outline-light">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container bg-white p-4 rounded shadow">
        <h2>Controle de Estoque: <?php echo $unit_name; ?></h2>
        
        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger text-center mt-3"><?php echo $error_message; ?></div>
        <?php endif; ?>
        
        <ul class="nav nav-tabs" id="myTab" role="tablist">
            <li class="nav-item"><a class="nav-link active" id="estoque-tab" data-bs-toggle="tab" href="#estoque">Estoque Atual</a></li>
            <li class="nav-item"><a class="nav-link" id="entrada-tab" data-bs-toggle="tab" href="#entrada">Registro de Entrada</a></li>
            <li class="nav-item"><a class="nav-link" id="saida-tab" data-bs-toggle="tab" href="#saida">Registro de Saída</a></li>
        </ul>
        <div class="tab-content p-3" id="myTabContent">
            <div class="tab-pane fade show active" id="estoque">
                <h3>Estoque Atual por Item</h3>
                <table class="table table-striped">
                    <thead><tr><th>Item</th><th>Categoria</th><th>Estoque</th><th>Ações</th></tr></thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT * FROM items");
                        while ($item = $result->fetch_assoc()) {
                            $stock = getStock($item['id'], $unit_name);
                            echo "<tr>
                                <td>{$item['name']}</td>
                                <td>{$item['category']}</td>
                                <td>" . $stock . " {$item['unit']}</td>
                                <td><button class='btn btn-sm btn-info text-white' data-bs-toggle='modal' data-bs-target='#historyModal' data-item-id='{$item['id']}' data-item-name='{$item['name']}' data-unit-name='{$unit_name}'>Ver Histórico</button></td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <div class="tab-pane fade" id="entrada">
                <h3>Registro de Entrada</h3>
                <form method="POST">
                    <input type="hidden" name="type" value="entrada">
                    <label class="form-label">Item:</label>
                    <select name="item_id" class="form-control mb-2" required>
                        <?php $result = $conn->query("SELECT * FROM items"); while ($item = $result->fetch_assoc()) echo "<option value='{$item['id']}'>{$item['name']}</option>"; ?>
                    </select>
                    <label class="form-label">Data:</label>
                    <input type="date" name="date" class="form-control mb-2" required>
                    <label class="form-label">Quantidade:</label>
                    <input type="number" name="quantity" placeholder="Quantidade" class="form-control mb-2" required min="1">
                    <button type="submit" class="btn btn-success">Adicionar Entrada</button>
                </form>
            </div>
            
            <div class="tab-pane fade" id="saida">
                <h3>Registro de Saída</h3>
                <form method="POST">
                    <input type="hidden" name="type" value="saida">
                    <label class="form-label">Item:</label>
                    <select name="item_id" class="form-control mb-2" required>
                        <?php $result = $conn->query("SELECT * FROM items"); while ($item = $result->fetch_assoc()) echo "<option value='{$item['id']}'>{$item['name']}</option>"; ?>
                    </select>
                    <label class="form-label">Data:</label>
                    <input type="date" name="date" class="form-control mb-2" required>
                    <label class="form-label">Quantidade:</label>
                    <input type="number" name="quantity" placeholder="Quantidade" class="form-control mb-2" required min="1">
                    <button type="submit" class="btn btn-danger">Adicionar Saída</button>
                </form>
            </div>
        </div>
    </div>
    
    <div class="modal fade" id="historyModal" tabindex="-1" aria-labelledby="historyModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg">
        <div class="modal-content">
          <div class="modal-header bg-primary text-white">
            <h5 class="modal-title" id="historyModalLabel">Histórico de Movimentação</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body">
            <h4 id="modal-item-name"></h4>
            <p>Unidade: **<?php echo $unit_name; ?>**</p>
            <table class="table table-bordered table-sm">
                <thead>
                    <tr><th>Data</th><th>Tipo</th><th>Quantidade</th></tr>
                </thead>
                <tbody id="history-table-body">
                    </tbody>
            </table>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
          </div>
        </div>
      </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    // Script para carregar o histórico via AJAX quando o modal é aberto
    var historyModal = document.getElementById('historyModal')
    historyModal.addEventListener('show.bs.modal', function (event) {
      var button = event.relatedTarget
      var itemId = button.getAttribute('data-item-id')
      var itemName = button.getAttribute('data-item-name')
      var unitName = button.getAttribute('data-unit-name')
      
      var modalTitle = historyModal.querySelector('#modal-item-name')
      modalTitle.textContent = 'Item: ' + itemName
      
      var tableBody = document.getElementById('history-table-body')
      tableBody.innerHTML = '<tr><td colspan="3" class="text-center">Carregando histórico...</td></tr>';
      
      fetch('history.php?item_id=' + itemId + '&unit_name=' + encodeURIComponent(unitName))
        .then(response => response.json())
        .then(data => {
          tableBody.innerHTML = '';
          if (data.length > 0) {
            data.forEach(function(row) {
              var rowClass = row.type === 'entrada' ? 'table-success' : 'table-danger';
              var newRow = tableBody.insertRow();
              newRow.className = rowClass;
              newRow.insertCell().textContent = row.date;
              newRow.insertCell().textContent = row.type.charAt(0).toUpperCase() + row.type.slice(1);
              newRow.insertCell().textContent = row.quantity;
            });
          } else {
            tableBody.innerHTML = '<tr><td colspan="3" class="text-center">Nenhum registro de movimentação encontrado.</td></tr>';
          }
        })
        .catch(error => {
          tableBody.innerHTML = '<tr><td colspan="3" class="text-center text-danger">Erro ao carregar os dados.</td></tr>';
          console.error('Fetch error:', error);
        });
    })
    </script>
</body>
</html>