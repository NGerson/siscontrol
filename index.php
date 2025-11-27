<?php
include 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // Hash simples
    $role = $_POST['role'];

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $password, $role);
    if ($stmt->execute()) {
        $message = "Cadastro realizado! <a href='login.php'>Faça login</a>";
    } else {
        $message = "Erro: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Cadastro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
   <link href="css/style.css" rel="stylesheet">





</head>
<body>
    <div class="main-banner">
    <div class="card shadow-lg p-3 mb-5 bg-white rounded" style="width: 100%; max-width: 450px;">
        <div class="card-body">
            <h2 class="card-title text-center mb-4 text-primary">📦 Cadastro de Usuário</h2>
            <?php if (isset($message)): ?>
                <div class="alert alert-info text-center"><?php echo $message; ?></div>
            <?php endif; ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label">Nome:</label>
                    <input type="text" name="name" class="form-control" required autocomplete="off">
                </div>
                <div class="mb-3">
                    <label class="form-label">Email:</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Senha:</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Função:</label>
                    <select name="role" class="form-control">
                        <option value="user">Usuário</option>
                        <option value="admin">Admin</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary w-100 mt-2">Cadastrar</button>
            </form>
            <p class="mt-3 text-center">Já tem conta? <a href="login.php">Faça Login</a></p>
        </div>
      </div>
   </div>
</body>
</html>