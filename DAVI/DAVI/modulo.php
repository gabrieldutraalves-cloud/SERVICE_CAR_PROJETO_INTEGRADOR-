<?php
declare(strict_types=1);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}
date_default_timezone_set('America/Sao_Paulo');
$config = require __DIR__ . '/config.php';
// Uma sessão pode ser compartilhada com o login da equipe.
$owner = (string) ($_SESSION['id_usuario'] ?? 'visitante');
$_SESSION['service_car'][$owner] ??= [
    'token' => bin2hex(random_bytes(32)),
    'pedidos' => [],
];
$modulo = &$_SESSION['service_car'][$owner];
function scHtml($valor): string {
    return htmlspecialchars((string) $valor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}
function scMoeda(int $centavos): string {
    return 'R$ ' . number_format($centavos / 100, 2, ',', '.');
}
function scFalha(string $texto, int $codigo = 400): void {
    http_response_code($codigo);
    exit(scHtml($texto) . ' <a href="historicoServico.php">Histórico</a> · <a href="agendar.php">Agendar</a>');
}
function scPost(): void {
    global $modulo;
    if (!is_string($_POST['token'] ?? null) || !hash_equals($modulo['token'], $_POST['token'])) {
        scFalha('Sessão expirada. Abra a página novamente.', 403);
    }
}
function scPedido(): array {
    global $modulo;
    $id = $_GET['id'] ?? '';
    if (!is_string($id) || !isset($modulo['pedidos'][$id])) {
        scFalha('Agendamento indisponível nesta sessão.', 404);
    }
    return $modulo['pedidos'][$id];
}
function scIr(string $pagina, string $id): void {
    header('Location: ' . $pagina . '?id=' . rawurlencode($id), true, 303);
    exit;
}
function scParcelas(int $valor, int $n): string {
    $base = intdiv($valor, $n);
    $resto = $valor % $n;
    if (!$resto) return $n . 'x de ' . scMoeda($base);
    return $resto . 'x de ' . scMoeda($base + 1) . ' + ' . ($n - $resto) . 'x de ' . scMoeda($base);
}
