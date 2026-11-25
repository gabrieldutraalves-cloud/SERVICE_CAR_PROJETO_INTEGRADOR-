<?php
require __DIR__ . '/modulo.php';
$selecionado = filter_input(INPUT_GET, 'servico', FILTER_VALIDATE_INT);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    scPost();
    $idServico = filter_input(INPUT_POST, 'servico', FILTER_VALIDATE_INT);
    $servico = $config['catalogo'][$idServico] ?? null;
    $veiculo = trim(is_string($_POST['veiculo'] ?? null) ? $_POST['veiculo'] : '');
    $quando = is_string($_POST['quando'] ?? null) ? $_POST['quando'] : '';
    $inicio = DateTimeImmutable::createFromFormat('!Y-m-d\TH:i', $quando);
    if (!$servico || !$veiculo || strlen($veiculo) > 160 || !$inicio ||
        $inicio->format('Y-m-d\TH:i') !== $quando || $inicio <= new DateTimeImmutable()) {
        scFalha('Confira o serviço, o veículo e escolha uma data futura.');
    }
    $id = $_POST['pedido'] ?? '';
    if (!is_string($id) || !preg_match('/^[a-f0-9]{32}$/D', $id) ||
        !is_string($_POST['assinatura'] ?? null) ||
        !hash_equals(hash_hmac('sha256', $id, $modulo['token']), $_POST['assinatura'])) {
        scFalha('Abra o formulário novamente.');
    }
    $fim = $inicio->modify('+' . $servico['duracao'] . ' minutes');
    $modulo['pedidos'][$id] ??= [
        'id' => $id, 'nome' => $servico['nome'], 'veiculo' => $veiculo,
        'valor' => $servico['valor'], 'preco' => $servico['valor'] / 100,
        'data' => $inicio->format('d/m/Y'), 'horario' => $inicio->format('H:i'),
        'termino' => $fim->format($inicio->format('Ymd') === $fim->format('Ymd') ? 'H:i' : 'd/m/Y H:i'),
        'status' => 'Pendente', 'etapa' => 0, 'metodo' => null, 'parcelas' => 1,
    ];
    scIr('pagamento.php', $id);
}
$id = bin2hex(random_bytes(16));
?>
<!doctype html><html lang="pt-BR"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1">
<title>Agendar | Service Car</title><link rel="stylesheet" href="cssDavi.css">
<body class="pagamento"><header class="cabecalho"><h1>Agendar serviço</h1></header><main>
<form class="caixa" method="post"><div class="campos">
<input type="hidden" name="token" value="<?= scHtml($modulo['token']) ?>">
<input type="hidden" name="pedido" value="<?= $id ?>">
<input type="hidden" name="assinatura" value="<?= hash_hmac('sha256', $id, $modulo['token']) ?>">
<label>Serviço <select name="servico" required>
<?php foreach ($config['catalogo'] as $codigo => $item): ?>
<option value="<?= $codigo ?>" <?= $selecionado === $codigo ? 'selected' : '' ?>><?= scHtml($item['nome']) ?> · <?= scMoeda($item['valor']) ?></option>
<?php endforeach ?></select></label>
<label>Veículo <input name="veiculo" required maxlength="80" placeholder="Modelo e ano"></label>
<label>Data e hora <input type="datetime-local" name="quando" required></label></div>
<button class="botao">Continuar para pagamento</button></form>
<nav class="navegacao"><a href="historicoServico.php">Histórico de serviços</a></nav></main></body></html>
