<?php
include 'db.php';
session_start();
$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // 1. Buscar o usuário pelo email
    $stmt = $conn->prepare("SELECT id, name, password, role FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        
        // 2. Verificar a senha (usando password_verify, pois a senha foi hasheada no cadastro)
        if (password_verify($password, $user['password'])) {
            
            // 3. Login bem-sucedido: Iniciar e salvar sessão
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_role'] = $user['role'];
            
            // Redirecionar para o painel de controle
            header('Location: dashboard.php');
            exit;
            
        } else {
            $message = "Senha incorreta.";
        }
    } else {
        $message = "Usuário não encontrado.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>
    <div class="main-banner"> 
        <div class="card shadow-lg p-3 mb-5 bg-white rounded" style="width: 100%; max-width: 450px;">
            <div class="card-body">
                <h2 class="card-title text-center mb-4 text-primary">🔐 Fazer Login</h2>
                <?php if (!empty($message)): ?>
                    <div class="alert alert-danger text-center"><?php echo $message; ?></div>
                <?php endif; ?>
                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label">Email:</label>
                        <input type="email" name="email" class="form-control" required autocomplete="off">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Senha:</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <button type="submit" class="btn btn-success w-100 mt-2">Entrar</button>
                </form>
                <p class="mt-3 text-center">Não tem conta? <a href="index.php">Cadastre-se</a></p>
            </div>
        </div>
    </div>
</body>
</html>