<?php
// Consultar e excluir formas demonstrativas de pagamento do próprio perfil.
require __DIR__ . '/conexao.php';
try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        conferirCsrf();
        $id = filter_var(campo('id'), FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
        if (!$id || campo('acao') !== 'excluir') falha(400, 'Operação inválida.');
        $consulta = $conexao->prepare('DELETE FROM dadoPagamento WHERE id = ? AND idDadoPessoal = ?');
        $consulta->execute([$id, $idUsuario]);
        redirecionar('dadoPagamento.php');
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') falha(405, 'Método não permitido.');
    $consulta = $conexao->prepare('SELECT id, tipo, ultimosDigitos, chavePixMascarada FROM dadoPagamento WHERE idDadoPessoal = ? ORDER BY id');
    $consulta->execute([$idUsuario]);
    $cartoes = '';
    $nomes = ['credito' => 'Cartão de Crédito', 'debito' => 'Cartão de Débito', 'pix' => 'Pix'];
    foreach ($consulta->fetchAll() as $dados) {
        $id = (int)$dados['id'];
        $pix = $dados['tipo'] === 'pix';
        $titulo = escapar($nomes[$dados['tipo']] ?? 'Pagamento');
        $icone = $pix ? '<span class="icone-pix" aria-hidden="true">▦</span>' : '<img class="icone-cartao" src="imagem_cartao.png" alt="">';
        $descricao = $pix ? 'Chave pix: ' . escapar($dados['chavePixMascarada']) : 'xxxx xxxx xxxx ' . escapar($dados['ultimosDigitos']);
        $csrf = escapar($_SESSION['csrf']);
        $cartoes .= <<<HTML
        <article>
            <div class="titulo-pagamento">$icone<h3>$titulo</h3></div>
            <p>$descricao</p>
            <form class="acoes-pagamento" method="post" action="PHP/dadoPagamento.php">
                <input type="hidden" name="csrf" value="$csrf">
                <input type="hidden" name="id" value="$id">
                <input type="hidden" name="acao" value="excluir">
                <a class="botao botao-pagamento" href="PHP/dadoPagamento_alteracao.php?id=$id">Alterar</a>
                <button class="botao botao-pagamento" type="submit">Excluir</button>
            </form>
        </article>
        HTML;
    }
    if ($cartoes === '') $cartoes = '<p>Nenhuma forma de pagamento cadastrada.</p>';
    $html = modelo('dadoPagamento.html');
    echo preg_replace_callback('/<!-- INICIO_PAGAMENTOS -->.*?<!-- FIM_PAGAMENTOS -->/s', fn() => $cartoes, $html, 1);
} catch (PDOException $erro) { erroBanco($erro); }
