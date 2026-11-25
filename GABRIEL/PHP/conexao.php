<?php
// Projeto escolar: execução exclusivamente local, sem dados reais.
declare(strict_types=1);
session_start();
header('Cache-Control: no-store');

function falha(int $status, string $mensagem): void {
    http_response_code($status);
    if (defined('API_JSON')) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['erro' => $mensagem], JSON_UNESCAPED_UNICODE);
    } else {
        header('Content-Type: text/plain; charset=utf-8');
        echo $mensagem;
    }
    exit;
}
if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    falha(403, 'Esta demonstração só funciona em localhost.');
}
// A integração com o login do grupo deverá substituir este usuário de teste.
$idUsuario = 1;
$_SESSION['csrf'] ??= bin2hex(random_bytes(32));

function conferirCsrf(): void {
    $token = $_POST['csrf'] ?? '';
    if (!is_string($token) || !hash_equals($_SESSION['csrf'], $token)) {
        falha(403, 'Sessão inválida. Volte à página e tente novamente.');
    }
}
function somentePost(): void {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') falha(405, 'Método não permitido.');
}
function campo(string $nome): string {
    $valor = $_POST[$nome] ?? '';
    return is_string($valor) ? trim($valor) : '';
}
function numeros(string $valor): string { return preg_replace('/\D/', '', $valor); }
function escapar(?string $valor): string { return htmlspecialchars($valor ?? '', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }
function jsonResposta(array $dados): void {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($dados, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
    exit;
}
function redirecionar(string $destino): void {
    header('Location: ' . $destino, true, 303);
    exit;
}
function modelo(string $arquivo): string {
    $html = file_get_contents(__DIR__ . '/../' . $arquivo);
    if ($html === false) falha(500, 'Modelo não encontrado.');
    // O HTML original é usado como modelo; caminhos relativos continuam válidos.
    $html = preg_replace('/<!-- REDIRECIONAMENTO_PHP -->.*?<!-- FIM_REDIRECIONAMENTO_PHP -->/s', '', $html);
    return str_replace('<head>', '<head>' . "\n    <base href=\"../\">", $html);
}
try {
    // Configuração padrão do WampServer. Ajuste a senha se tiver definido uma.
    $conexao = new PDO('mysql:host=localhost;dbname=servicecar_db;charset=utf8mb4', 'root', '', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false
    ]);
} catch (PDOException $erro) {
    error_log($erro->getMessage());
    falha(503, 'Não foi possível conectar ao servicecar_db. Confira o MySQL e o banco no phpMyAdmin.');
}
function erroBanco(PDOException $erro): void {
    error_log($erro->getMessage());
    if ($erro->getCode() === '23000') falha(409, 'Dados duplicados ou cadastro pessoal ainda não criado.');
    falha(500, 'Não foi possível acessar os dados. Confira as tabelas no phpMyAdmin.');
}
