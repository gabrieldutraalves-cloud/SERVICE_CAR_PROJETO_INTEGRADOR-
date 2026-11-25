<?php
require __DIR__ . '/modulo.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title>Histórico de Serviços | Service Car</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="cssDavi.css">
</head>

<body class="historico">

    <!-- ===== CABEÇALHO ===== -->
    <header class="cabecalho">
        <a href="<?= scHtml($config['inicio']) ?>"><?php if (is_file(__DIR__ . '/img/logo_sc.png')): ?><img class="logo" src="img/logo_sc.png" alt="Service Car"><?php else: ?>Service Car<?php endif ?></a>

        <h1>Histórico de serviços</h1>

        <div class="espaco-logo" aria-hidden="true"></div>
    </header>

    <!-- ===== CONTEÚDO PRINCIPAL ===== -->
    <main class="painel">

        <!-- ===== RESUMO DOS SERVIÇOS ===== -->
        <section class="resumo">
            <h2>Acompanhe seus serviços</h2>

            <div class="indicadores">
                <div class="indicador"><strong>Total</strong><span id="total"></span></div>
                <div class="indicador"><strong>Em andamento</strong><span id="andamento"></span></div>
                <div class="indicador"><strong>Finalizados</strong><span id="finalizados"></span></div>
            </div>

            <label class="pesquisa-label" for="pesquisa">Pesquisar serviço</label>
            <input type="search" id="pesquisa" placeholder="Pesquisar serviço..." autocomplete="off">
        </section>

        <!-- ===== FILTROS ===== -->
        <nav class="filtros" aria-label="Filtrar serviços">
            <button type="button" class="filtro" data-filtro="Pendente" aria-pressed="false">Pendentes</button>
            <button type="button" class="filtro ativo" data-filtro="Todos" aria-pressed="true">Todos</button>
            <button type="button" class="filtro" data-filtro="Em andamento" aria-pressed="false">Em andamento</button>
            <button type="button" class="filtro" data-filtro="Finalizado" aria-pressed="false">Finalizados</button>
            <button type="button" class="filtro" data-filtro="Cancelado" aria-pressed="false">Cancelados</button>
        </nav>

        <!-- ===== LISTA DE SERVIÇOS ===== -->
        <section id="lista-servicos" class="lista-servicos" aria-live="polite"></section>

        <!-- ===== RODAPÉ ===== -->
        <footer class="rodape">
            <a href="<?= scHtml($config['servicos']) ?>">Página de serviços</a>
        </footer>

    </main>

    <!-- ===== JANELA DE DETALHES ===== -->
   


    <script type="application/json" id="dados-historico"><?= json_encode(array_values(array_reverse($modulo['pedidos'])), JSON_HEX_TAG | JSON_INVALID_UTF8_SUBSTITUTE) ?></script>
    <script src="scriptdavi.js" defer></script>
</body>
</html>
