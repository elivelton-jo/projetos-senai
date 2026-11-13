<?php
$host = 'localhost';
$usuario = 'root';
$senha = '';
$banco = 'barbearia_db'; // Certifica-te de que está exatamente com este nome

try {
    $pdo = new PDO("mysql:host=$host;dbname=$banco;charset=utf8mb4", $usuario, $senha);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro crítico na conexão com o banco de dados: " . $e->getMessage());
}
?>