<?php
include 'db.php';

// Criar tabelas
$sql = "
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('user', 'admin') DEFAULT 'user'
);

CREATE TABLE IF NOT EXISTS items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    category ENUM('alimentacao', 'higiene') NOT NULL,
    unit VARCHAR(50) DEFAULT 'unidade'
);

CREATE TABLE IF NOT EXISTS inventory (
    id INT AUTO_INCREMENT PRIMARY KEY,
    item_id INT NOT NULL,
    quantity INT NOT NULL,
    date DATE NOT NULL,
    type ENUM('entrada', 'saida') NOT NULL,
    -- NOVO CAMPO: Para identificar a unidade
    unit_name ENUM('SCFV SEDE', 'TRAB EDU', 'SCFV RIO BRANCO') NOT NULL,
    FOREIGN KEY (item_id) REFERENCES items(id)
);
";

// Executar as criações e garantir a conexão
if ($conn->multi_query($sql)) {
    do {
        // Limpar resultados de consultas anteriores
        if ($result = $conn->store_result()) {
            $result->free();
        }
    } while ($conn->more_results() && $conn->next_result());
} else {
    echo "Erro ao criar tabelas: " . $conn->error;
}


// Inserir itens de exemplo e remover a cláusula IGNORE para garantir a atualização do ENUM
$conn->query("
    INSERT IGNORE INTO items (name, category, unit) 
    VALUES 
        ('Arroz', 'alimentacao', 'kg'), 
        ('Sabão', 'higiene', 'barra'),
        ('Feijão', 'alimentacao', 'kg'),
        ('Pasta de Dente', 'higiene', 'unidade')
");

echo "Tabelas criadas e itens de exemplo inseridos com sucesso!";
?>