<?php
require __DIR__ . '/modulo.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') scFalha('Use a página de pagamento.', 405);
scPost();
$p = scPedido();
$metodo = $_POST['metodo'] ?? '';
$n = filter_input(INPUT_POST, 'parcelas', FILTER_VALIDATE_INT);
if (!in_array($metodo, ['Crédito', 'Débito', 'Pix'], true) ||
    !in_array($n, $metodo === 'Crédito' ? [1, 2, 3] : [1], true)) scFalha('Forma de pagamento inválida.');
if ($p['status'] === 'Pendente' && $p['metodo'] === null) {
    $registro = &$modulo['pedidos'][$p['id']];
    $registro['metodo'] = $metodo;
    $registro['parcelas'] = $n;
}
scIr('statusServico.php', $p['id']);
