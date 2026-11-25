<?php
require __DIR__ . '/modulo.php';
if (!isset($_GET['id'])) { header('Location: agendar.php'); exit; }
$p = scPedido();
if ($p['metodo'] !== null || $p['status'] === 'Cancelado') scIr('statusServico.php', $p['id']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Confirmar pagamento | Service Car</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="cssDavi.css">
</head>
<body class="pagamento">
    <!-- ===== CABEÇALHO ===== -->
    <header class="cabecalho">
        <a href="<?= scHtml($config['inicio']) ?>"><?php if (is_file(__DIR__ . '/img/logo_sc.png')): ?><img class="logo" src="img/logo_sc.png" alt="Service Car"><?php else: ?>Service Car<?php endif ?></a>
        <h1 id="title">Confirmar pagamento</h1>
        <div class="espaco" aria-hidden="true"></div>
    </header>
    <!-- ===== CONTEÚDO PRINCIPAL ===== -->
    <main>
        <!-- ===== RESUMO DO SERVIÇO ===== -->
        <section class="caixa resumo" aria-label="Resumo do serviço">
            <p><span>Serviço agendado:</span> <?= scHtml($p['nome']) ?></p>
            <p><span>Veículo:</span> <?= scHtml($p['veiculo']) ?></p>
            <p><span>Data e hora:</span> <?= scHtml($p['data'] . ' às ' . $p['horario']) ?></p>
            <p><span>Valor a pagar:</span> <b><?= scMoeda($p['valor']) ?></b></p>
        </section>
        <form id="confirmacao" method="post" action="confirmar.php?id=<?= scHtml($p['id']) ?>">
            <input type="hidden" name="token" value="<?= scHtml($modulo['token']) ?>">
            <input type="hidden" name="metodo"><input type="hidden" name="parcelas">
        </form>
        <p>Pagamento simulado. Use dados fictícios.</p>
        <h2>Selecione a forma de pagamento</h2>
        <!-- ===== CRÉDITO ===== -->
        <form class="caixa forma-pagamento" data-m="Crédito" method="dialog" autocomplete="off" novalidate>
            <h3>Crédito <button type="button" class="alterar">Alterar cartão</button></h3>
            <div class="campos">
                <label>Cartão
                    <input name="cartao" inputmode="numeric" maxlength="19" autocomplete="cc-number" placeholder="0000 0000 0000 0000" data-card>
                </label>
                <label>CVC
                    <input name="cvc" inputmode="numeric" maxlength="3" autocomplete="cc-csc" placeholder="000" data-cvc>
                </label>
                <label>Titular
                    <input name="titular" autocomplete="cc-name" placeholder="Nome do titular do cartão">
                </label>
                <label>Parcelas
                    <select name="parcelas">
                        <?php for ($n = 1; $n <= 3; $n++): ?>
                        <option value="<?= $n ?>"><?= scParcelas($p['valor'], $n) ?></option>
                        <?php endfor ?>
                    </select>
                </label>
            </div>
            <button class="botao" type="submit">Confirmar pagamento</button>
            <p class="erro" role="alert"></p>
        </form>
        <!-- ===== DÉBITO ===== -->
        <form class="caixa forma-pagamento" data-m="Débito" method="dialog" autocomplete="off" novalidate>
            <h3>Débito <button type="button" class="alterar">Alterar cartão</button></h3>
            <div class="campos">
                <label>Cartão
                    <input name="cartao" inputmode="numeric" maxlength="19" autocomplete="cc-number" placeholder="0000 0000 0000 0000" data-card>
                </label>
                <label>CVC
                    <input name="cvc" inputmode="numeric" maxlength="3" autocomplete="cc-csc" placeholder="000" data-cvc>
                </label>
                <label>Titular
                    <input name="titular" autocomplete="cc-name" placeholder="Nome do titular do cartão">
                </label>
            </div>
            <button class="botao" type="submit">Confirmar pagamento</button>
            <p class="erro" role="alert"></p>
        </form>
        <!-- ===== PIX ===== -->
        <form class="caixa forma-pagamento" data-m="Pix" method="dialog" autocomplete="off" novalidate>
            <h3>Pix</h3>
            <div class="campos">
                <label>CPF
                    <input name="cpf" inputmode="numeric" maxlength="14" placeholder="000.000.000-00" data-cpf>
                </label>
                <label>E-mail
                    <input name="email" type="email" autocomplete="email" placeholder="voce@email.com">
                </label>
                <label>Telefone
                    <input name="telefone" type="tel" maxlength="15" autocomplete="tel" placeholder="(31) 90000-0000">
                </label>
            </div>
            <button class="botao" type="submit">Confirmar pagamento</button>
            <p class="erro" role="alert"></p>
        </form>
        <!-- ===== NAVEGAÇÃO ===== -->
        <nav class="navegacao">
            <a href="<?= scHtml($config['servicos']) ?>">Página de serviços</a>
            <a href="historicoServico.php">Histórico de serviços</a>
        </nav>
    </main>
    <script src="scriptdavi.js" defer></script>
</body>
</html>
