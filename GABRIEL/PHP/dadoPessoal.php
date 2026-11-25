<?php
// Consultar dados pessoais do perfil demonstrativo.
define('API_JSON', true);
require __DIR__ . '/conexao.php';
if ($_SERVER['REQUEST_METHOD'] !== 'GET') falha(405, 'Método não permitido.');
try {
    $consulta = $conexao->prepare('SELECT nome, cpf, dataNascimento, email, telefone, cep, rua, numero, bairro, cidade, estado, foto FROM dadoPessoal WHERE id = ?');
    $consulta->execute([$idUsuario]);
    jsonResposta(['dados' => $consulta->fetch() ?: null, 'csrf' => $_SESSION['csrf']]);
} catch (PDOException $erro) { erroBanco($erro); }
