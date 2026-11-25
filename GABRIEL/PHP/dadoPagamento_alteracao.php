<?php
// Inserir ou atualizar apenas metadados de pagamento fictícios, sem transações.
require __DIR__ . '/conexao.php';
try {
    $idRecebido = $_SERVER['REQUEST_METHOD'] === 'POST' ? campo('id') : ($_GET['id'] ?? '');
    $id = $idRecebido === '' ? null : filter_var($idRecebido, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);
    if ($id === false) falha(400, 'Identificador inválido.');
    $dados = [];
    if ($id !== null) {
        $consulta = $conexao->prepare('SELECT * FROM dadoPagamento WHERE id=? AND idDadoPessoal=?');
        $consulta->execute([$id, $idUsuario]);
        $dados = $consulta->fetch();
        if (!$dados) falha(404, 'Forma de pagamento não encontrada.');
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        conferirCsrf();
        $tipo = campo('tipo');
        $titular = $digitos = $mes = $ano = $pix = null;
        if ($tipo === 'pix') {
            $pix = campo('chavePix');
            // Aceita somente um identificador já mascarado, não uma chave verdadeira.
            if (!preg_match('/^[a-zA-Z0-9*._@+-]{3,120}$/D', $pix) || !str_contains($pix, '***')) falha(400, 'Informe apenas uma chave fictícia mascarada, como cli***@example.com.');
        } elseif (in_array($tipo, ['credito', 'debito'], true)) {
            $titular = campo('titular');
            $digitos = campo('cartao');
            if ($titular === '' || strlen($titular) > 120 || !preg_match('/^\d{4}$/D', $digitos)
                || !preg_match('/^(0[1-9]|1[0-2])\/(\d{2})$/D', campo('validade'), $validade)) falha(400, 'Informe nome fictício, quatro dígitos e validade MM/AA.');
            $mes = (int)$validade[1];
            $ano = 2000 + (int)$validade[2];
        } else { falha(400, 'Tipo de pagamento inválido.'); }
        $valores = [$tipo, $titular, $digitos, $mes, $ano, $pix];
        if ($id === null) {
            $sql = 'INSERT INTO dadoPagamento (tipo,titular,ultimosDigitos,validadeMes,validadeAno,chavePixMascarada,idDadoPessoal) VALUES (?,?,?,?,?,?,?)';
            $valores[] = $idUsuario;
        } else {
            $sql = 'UPDATE dadoPagamento SET tipo=?,titular=?,ultimosDigitos=?,validadeMes=?,validadeAno=?,chavePixMascarada=? WHERE id=? AND idDadoPessoal=?';
            $valores[] = $id;
            $valores[] = $idUsuario;
        }
        $consulta = $conexao->prepare($sql);
        $consulta->execute($valores);
        redirecionar('dadoPagamento.php');
    }
    if ($_SERVER['REQUEST_METHOD'] !== 'GET') falha(405, 'Método não permitido.');
    $tipo = $dados['tipo'] ?? 'credito';
    $validade = isset($dados['validadeMes'], $dados['validadeAno']) ? sprintf('%02d/%02d', $dados['validadeMes'], $dados['validadeAno'] % 100) : '';
    $html = modelo('dadoPagamento_alteracao.html');
    $html = strtr($html, [
        '{{CSRF}}' => escapar($_SESSION['csrf']), '{{ID}}' => escapar((string)$id),
        '{{CREDITO}}' => $tipo === 'credito' ? 'checked' : '',
        '{{DEBITO}}' => $tipo === 'debito' ? 'checked' : '',
        '{{PIX}}' => $tipo === 'pix' ? 'checked' : '',
        '{{TITULAR}}' => escapar($dados['titular'] ?? ''),
        '{{DIGITOS}}' => escapar($dados['ultimosDigitos'] ?? ''),
        '{{VALIDADE}}' => escapar($validade), '{{CHAVEPIX}}' => escapar($dados['chavePixMascarada'] ?? ''),
        '{{BOTAO}}' => $id === null ? 'Adicionar' : 'Salvar'
    ]);
    echo $html;
} catch (PDOException $erro) { erroBanco($erro); }
