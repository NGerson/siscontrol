<?php
include 'db.php';
session_start();
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Lista de todas as unidades para o filtro
$all_units = ['SCFV SEDE', 'TRAB EDU', 'SCFV RIO BRANCO'];

// 1. Lógica de Filtro
$selected_unit = $_GET['unit'] ?? 'all';

// Função para obter o estoque
function getStock($item_id, $unit_name = 'all') {
    global $conn;
    $sql = "SELECT SUM(CASE WHEN type='entrada' THEN quantity ELSE -quantity END) AS stock FROM inventory WHERE item_id = ?";
    
    if ($unit_name !== 'all') {
        $sql .= " AND unit_name = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $item_id, $unit_name);
    } else {
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $item_id);
    }
    
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc()['stock'] ?? 0;
}

// Lógica de manipulação de itens (apenas se for necessário no admin, mantido do código anterior)
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'add_item' && isset($_POST['name'], $_POST['category'], $_POST['unit'])) {
        $name = $_POST['name'];
        $category = $_POST['category'];
        $unit = $_POST['unit'];
        
        $stmt = $conn->prepare("INSERT INTO items (name, category, unit) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $name, $category, $unit);
        $stmt->execute();
        header("Location: dashboard.php");
        exit();
    }
    // Adicione lógica para editar/deletar itens aqui se necessário
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin</title>
    <link rel="icon" type="image/png" href="img/control2.1.png"> 
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet"> 
    
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
            <a class="navbar-brand" href="dashboard.php">
                <img src="img/control2.1.png" alt="Logo S Control" style="height: 30px; margin-right: 8px; border-radius: 5px;">
                Dashboard Administrador
            </a>
            <div class="d-flex">
                <a href="units.php" class="btn btn-outline-light me-2">Unidades (Operacional)</a>
                <a href="logout.php" class="btn btn-outline-light">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container bg-white p-4 rounded shadow">
        <h2>Visão Geral do Estoque e Histórico</h2>

        <div class="row mb-4">
            <div class="col-md-6">
                <label for="unit-filter" class="form-label">Filtrar por Unidade:</label>
                <select id="unit-filter" class="form-select" onchange="window.location.href='dashboard.php?unit=' + this.value">
                    <option value="all" <?php echo $selected_unit === 'all' ? 'selected' : ''; ?>>Todas as Unidades (Consolidado)</option>
                    <?php foreach ($all_units as $unit): ?>
                        <option value="<?php echo $unit; ?>" <?php echo $selected_unit === $unit ? 'selected' : ''; ?>>
                            <?php echo $unit; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        
        <ul class="nav nav-tabs" id="adminTabs" role="tablist">
            <li class="nav-item"><a class="nav-link active" id="estoque-tab" data-bs-toggle="tab" href="#estoque">Estoque Consolidado</a></li>
            <li class="nav-item"><a class="nav-link" id="historico-tab" data-bs-toggle="tab" href="#historico">Histórico Global de Movimentação</a></li>
            <li class="nav-item"><a class="nav-link" id="cadastro-tab" data-bs-toggle="tab" href="#cadastro">Gerenciar Itens</a></li>
        </ul>

        <div class="tab-content p-3" id="adminTabsContent">
            
            <div class="tab-pane fade show active" id="estoque">
                <h3>Estoque Atual - <?php echo $selected_unit === 'all' ? 'Todas as Unidades' : $selected_unit; ?></h3>
                <table class="table table-striped table-hover">
                    <thead><tr><th>Item</th><th>Categoria</th><th>Estoque Atual</th></tr></thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT * FROM items ORDER BY name ASC");
                        while ($item = $result->fetch_assoc()) {
                            // Usa a função getStock com o filtro de unidade
                            $stock = getStock($item['id'], $selected_unit);
                            $stock_class = $stock < 5 ? 'text-danger fw-bold' : '';
                            echo "<tr>
                                <td>{$item['name']}</td>
                                <td>{$item['category']}</td>
                                <td class='{$stock_class}'>" . $stock . " {$item['unit']}</td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
            <div class="tab-pane fade" id="historico">
                <h3>Histórico Completo de Movimentação</h3>
                <p class="text-muted">Últimos 100 registros de <?php echo $selected_unit === 'all' ? 'todas as unidades' : $selected_unit; ?></p>
                <div class="table-responsive">
                    <table class="table table-striped table-bordered table-sm">
                        <thead>
                            <tr><th>Data</th><th>Item</th><th>Unidade</th><th>Tipo</th><th>Quantidade</th></tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "
                                SELECT 
                                    i.date, 
                                    it.name AS item_name, 
                                    i.unit_name, 
                                    i.type, 
                                    i.quantity
                                FROM inventory i
                                JOIN items it ON i.item_id = it.id
                            ";
                            
                            // Aplica filtro de unidade
                            $params = [];
                            $types = '';
                            if ($selected_unit !== 'all') {
                                $sql .= " WHERE i.unit_name = ?";
                                $params[] = $selected_unit;
                                $types .= 's';
                            }
                            
                            $sql .= " ORDER BY i.date DESC LIMIT 100";
                            
                            $stmt = $conn->prepare($sql);
                            if (!empty($params)) {
                                $stmt->bind_param($types, ...$params);
                            }
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            if ($result->num_rows > 0):
                                while ($row = $result->fetch_assoc()):
                                    $row_class = $row['type'] === 'entrada' ? 'table-success' : 'table-danger';
                                    ?>
                                    <tr class="<?php echo $row_class; ?>">
                                        <td><?php echo date('d/m/Y', strtotime($row['date'])); ?></td>
                                        <td><?php echo $row['item_name']; ?></td>
                                        <td><?php echo $row['unit_name']; ?></td>
                                        <td><?php echo ucfirst($row['type']); ?></td>
                                        <td><?php echo $row['quantity']; ?></td>
                                    </tr>
                                <?php endwhile; 
                            else: ?>
                                <tr><td colspan="5" class="text-center">Nenhuma movimentação registrada.</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
            
            <div class="tab-pane fade" id="cadastro">
                <h3>Cadastro de Novos Itens de Estoque</h3>
                <p class="text-muted">Itens cadastrados aqui estarão disponíveis para todas as unidades.</p>
                <form method="POST">
                    <input type="hidden" name="action" value="add_item">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Nome do Item:</label>
                            <input type="text" name="name" class="form-control" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Categoria:</label>
                            <select name="category" class="form-select" required>
                                <option value="alimentacao">Alimentação</option>
                                <option value="higiene">Higiene</option>
                            </select>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label class="form-label">Unidade de Medida:</label>
                            <input type="text" name="unit" class="form-control" placeholder="Ex: kg, unidade, litro" required>
                        </div>
                        <div class="col-md-2 d-flex align-items-end mb-3">
                            <button type="submit" class="btn btn-primary w-100">Cadastrar Item</button>
                        </div>
                    </div>
                </form>
                
                <h4 class="mt-4">Itens Atuais</h4>
                <table class="table table-sm table-bordered">
                    <thead><tr><th>ID</th><th>Item</th><th>Categoria</th><th>Unidade</th></tr></thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT * FROM items");
                        while ($item = $result->fetch_assoc()) {
                            echo "<tr>
                                <td>{$item['id']}</td>
                                <td>{$item['name']}</td>
                                <td>{$item['category']}</td>
                                <td>{$item['unit']}</td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
            
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>