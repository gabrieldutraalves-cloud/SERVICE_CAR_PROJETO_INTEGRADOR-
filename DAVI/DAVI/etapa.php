<?php
require __DIR__ . '/modulo.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') scFalha('Utilize o formulário de status.', 405);
scPost();
if (!$config['demonstracao']) scFalha('Controle de demonstração desativado.', 403);
$p = scPedido();
$nova = $_POST['etapa'] ?? '';
$anterior = $_POST['anterior'] ?? '';
if ($p['status'] === 'Cancelado' || $p['status'] === 'Finalizado' || $anterior !== (string) $p['etapa']) {
    scIr('statusServico.php', $p['id']);
}
$registro = &$modulo['pedidos'][$p['id']];
if ($nova === 'cancelar') {
    $registro['status'] = 'Cancelado';
} elseif ($nova === 'avancar' && $p['metodo'] !== null) {
    $registro['etapa']++;
    $registro['status'] = $registro['etapa'] === 4 ? 'Finalizado' : 'Em andamento';
} else {
    scFalha('Confirme o pagamento de teste antes de avançar.');
}
scIr('statusServico.php', $p['id']);
