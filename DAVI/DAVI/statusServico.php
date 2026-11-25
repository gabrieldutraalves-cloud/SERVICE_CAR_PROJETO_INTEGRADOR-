<?php
require __DIR__ . '/modulo.php';
if (!isset($_GET['id'])) { header('Location: historicoServico.php'); exit; }
$p = scPedido();
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Status do Serviço | Service Car</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="cssDavi.css">
</head>

<body class="status-servico">

    <!-- ===== CABEÇALHO ===== -->
    <header class="cabecalho">
        <a href="<?= scHtml($config['inicio']) ?>"><?php if (is_file(__DIR__ . '/img/logo_sc.png')): ?><img class="logo" src="img/logo_sc.png" alt="Service Car"><?php else: ?>Service Car<?php endif ?></a>

        <h1>Status do serviço</h1>

        <div class="espaco" aria-hidden="true"></div>
    </header>

    <!-- ===== CONTEÚDO PRINCIPAL ===== -->
    <main class="painel">

        <!-- ===== PROGRESSO DO SERVIÇO ===== -->
        <section class="progresso" aria-label="Progresso do serviço">
            <?php if ($p['status'] === 'Cancelado'): ?><p>Serviço cancelado</p><?php else: ?>
            <div class="linha-progresso" style="--progresso: <?= $p['etapa'] / 4 ?>">

                <div class="etapa <?= $p['etapa'] > 0 ? 'concluida' : ($p['etapa'] === 0 ? 'atual' : '') ?>" <?= $p['etapa'] === 0 ? 'aria-current="step"' : '' ?>>
                    <p>Serviço pendente</p>
                    <span class="circulo"></span>
                </div>

                <div class="etapa <?= $p['etapa'] > 1 ? 'concluida' : ($p['etapa'] === 1 ? 'atual' : '') ?>" <?= $p['etapa'] === 1 ? 'aria-current="step"' : '' ?>>
                    <p>Serviço iniciado</p>
                    <span class="circulo"></span>
                </div>

                <div class="etapa <?= $p['etapa'] > 2 ? 'concluida' : ($p['etapa'] === 2 ? 'atual' : '') ?>" <?= $p['etapa'] === 2 ? 'aria-current="step"' : '' ?>>
                    <p>Andamento</p>
                    <span class="circulo"></span>
                </div>

                <div class="etapa <?= $p['etapa'] > 3 ? 'concluida' : ($p['etapa'] === 3 ? 'atual' : '') ?>" <?= $p['etapa'] === 3 ? 'aria-current="step"' : '' ?>>
                    <p>Últimos ajustes</p>
                    <span class="circulo"></span>
                </div>

                <div class="etapa <?= $p['etapa'] > 4 ? 'concluida' : ($p['etapa'] === 4 ? 'atual' : '') ?>" <?= $p['etapa'] === 4 ? 'aria-current="step"' : '' ?>>
                    <p>Serviço finalizado</p>
                    <span class="circulo"></span>
                </div>

            </div>
        <?php endif ?></section>

        <!-- ===== PREVISÃO DE HORÁRIO ===== -->
        <section class="previsao">
            <p>Previsão de horário:</p>
            <strong id="previsao-horario"><?= scHtml($p['horario'] . ' - ' . $p['termino']) ?></strong>
        </section>

        <!-- ===== INFORMAÇÕES DO SERVIÇO ===== -->
        <section class="informacoes">
            <div class="card-servico">

                <?php if (is_file(__DIR__ . '/img/polimento.jpg') && $p['nome'] === 'Polimento Automotivo'): ?><div class="imagem-servico">
                    <img src="img/polimento.jpg" alt="Imagem do serviço automotivo">
                </div>

                <?php endif ?><div class="dados-servico">
                    <p><strong>Veículo:</strong> <?= scHtml($p['veiculo']) ?></p>
                    <p><strong>Valor:</strong> <?= scMoeda($p['valor']) ?></p>
                    <?php if ($p['metodo'] === 'Crédito'): ?><p>Parcelas: <?= scParcelas($p['valor'], $p['parcelas']) ?></p><?php endif ?>
                    <p><strong>Serviço:</strong> <span id="nome-servico"><?= scHtml($p['nome']) ?></span></p>
                    <p><strong>Empresa:</strong> <span id="empresa"><?= scHtml($config['empresa']) ?></span></p>
                    <p><strong>Horário:</strong> <span id="horario"><?= scHtml($p['data'] . ' às ' . $p['horario']) ?></span></p>
                    <p><strong>Local:</strong> <span id="local"><?= scHtml($config['local']) ?></span></p>
                </div>

            </div>
        </section>

        <!-- ===== INFORMAÇÕES DO PAGAMENTO ===== -->
        <section class="info-pagamento">

            <div class="pagamento-linha">
                <strong>Forma de pagamento:</strong>
                <span id="forma-pagamento"><?= scHtml($p['metodo'] ?? 'A escolher') ?></span>
            </div>

            <div class="pagamento-linha">
                <strong>Status do pagamento:</strong>
                <span id="status-pagamento" class="selo"><?= scHtml($p['metodo'] ? 'Confirmado em simulação' : 'Pendente') ?></span>
            </div>

        </section>

        <!-- ===== RODAPÉ ===== -->
        <footer class="rodape">
            <a href="<?= scHtml($config['servicos']) ?>">Página de serviços</a>
            <?php if (!$p['metodo'] && $p['status'] !== 'Cancelado'): ?><a href="pagamento.php?id=<?= scHtml($p['id']) ?>">Ir para pagamento</a><?php endif ?>
            <a href="historicoServico.php">Histórico de serviços →</a>
        </footer>

    <?php if ($config['demonstracao'] && !in_array($p['status'], ['Finalizado', 'Cancelado'], true)): ?>
        <form method="post" action="etapa.php?id=<?= scHtml($p['id']) ?>">
            <fieldset><legend>Controles de demonstração</legend>
                <input type="hidden" name="token" value="<?= scHtml($modulo['token']) ?>">
                <input type="hidden" name="anterior" value="<?= $p['etapa'] ?>">
                <?php if ($p['metodo']): ?><button name="etapa" value="avancar">Avançar etapa</button><?php endif ?>
                <button name="etapa" value="cancelar">Cancelar serviço</button>
            </fieldset>
        </form>
        <?php endif ?>
    </main>

    <script src="scriptdavi.js" defer></script>
</body>
</html>
