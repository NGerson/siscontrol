<?php
include 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) header("Location: login.php");

// Definindo as unidades disponíveis
$units = [
    ['name' => 'SCFV SEDE', 'file' => 'scfv_sede.php'],
    ['name' => 'TRAB EDU', 'file' => 'trab_edu.php'],
    ['name' => 'SCFV RIO BRANCO', 'file' => 'scfv_rio_branco.php'],
];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Seleção de Unidade</title>
    <link rel="icon" type="image/png" href="img/control2.1.png"> 
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet"> 
    
    <style>
        body {
            /* ATENÇÃO: Mude este caminho para o local real da sua imagem */
            background-image: url('img/background.jpg'); 
        }
        .unit-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            cursor: pointer;
            border-left: 5px solid var(--primary-color);
        }
        .unit-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm mb-5">
        <div class="container">
            <a class="navbar-brand" href="units.php">
                <img src="img/control2.1.png" alt="Logo S Control" style="height: 30px; margin-right: 8px; border-radius: 5px;">
                Controle de Unidades
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
        <h2>Selecione a Unidade para Controle</h2>
        <p class="text-secondary">Clique na unidade para acessar o controle de estoque, entradas e saídas.</p>
        <hr>
        
        <div class="row g-4 mt-3">
            <?php foreach ($units as $unit): ?>
            <div class="col-md-4">
                <a href="<?php echo $unit['file']; ?>" class="text-decoration-none">
                    <div class="card p-3 unit-card shadow-sm">
                        <div class="card-body">
                            <h5 class="card-title text-primary"><i class="fas fa-warehouse me-2"></i> <?php echo $unit['name']; ?></h5>
                            <p class="card-text text-muted">Controle individual de entradas, saídas e visualização de estoque.</p>
                            <span class="btn btn-sm btn-outline-primary">Acessar</span>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/js/all.min.js"></script>
</body>
</html>