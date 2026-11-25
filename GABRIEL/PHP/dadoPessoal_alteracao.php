<?php
// Criar o perfil de teste na primeira edição; depois atualizar o mesmo registro.
define('API_JSON', true);
require __DIR__ . '/conexao.php';
somentePost();
conferirCsrf();
$nome = campo('nome');
$cpf = numeros(campo('cpf'));
$nascimento = campo('dataNascimento');
$email = campo('email');
$telefone = numeros(campo('telefone'));
$cep = numeros(campo('cep'));
$rua = campo('rua');
$numero = campo('numero');
$bairro = campo('bairro');
$cidade = campo('cidade');
$estado = strtoupper(campo('estado'));
$data = DateTimeImmutable::createFromFormat('!Y-m-d', $nascimento);
foreach ([[$nome, 120], [$rua, 150], [$numero, 20], [$bairro, 100], [$cidade, 100]] as [$valor, $limite]) {
    if (trim((string)$valor) === '' || strlen((string)$valor) > $limite) falha(400, 'Confira os campos de texto.');
}
if (strlen($cpf) !== 11 || strlen($cep) !== 8 || !in_array(strlen($telefone), [10, 11], true)
    || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 254
    || !preg_match('/^[A-Z]{2}$/D', $estado) || !$data || $data->format('Y-m-d') !== $nascimento
    || $data > new DateTimeImmutable('today')) falha(400, 'Confira CPF, CEP, telefone, e-mail, estado e nascimento.');
// Foto fictícia: data URL validada; a coluna foto deve comportar o conteúdo (MEDIUMTEXT).
$foto = campo('foto');
if ($foto !== '') {
    if (strlen($foto) > 1400000 || !preg_match('#^data:image/(png|jpeg|webp);base64,([A-Za-z0-9+/=]+)$#D', $foto, $partes)) falha(400, 'Foto inválida.');
    $bytes = base64_decode($partes[2], true);
    $imagem = $bytes === false ? false : @getimagesizefromstring($bytes);
    if (!$imagem || strlen($bytes) > 1048576 || $imagem['mime'] !== 'image/' . $partes[1] || $imagem[0] > 4096 || $imagem[1] > 4096) falha(400, 'Foto inválida ou acima do limite.');
}
$valores = [$nome, $cpf, $nascimento, $email, $telefone, $cep, $rua, $numero, $bairro, $cidade, $estado, $foto ?: null];
try {
    $consulta = $conexao->prepare('SELECT id FROM dadoPessoal WHERE id = ?');
    $consulta->execute([$idUsuario]);
    if ($consulta->fetch()) {
        $sql = 'UPDATE dadoPessoal SET nome=?, cpf=?, dataNascimento=?, email=?, telefone=?, cep=?, rua=?, numero=?, bairro=?, cidade=?, estado=?, foto=? WHERE id=?';
        $valores[] = $idUsuario;
    } else {
        $sql = 'INSERT INTO dadoPessoal (nome,cpf,dataNascimento,email,telefone,cep,rua,numero,bairro,cidade,estado,foto,id) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)';
        $valores[] = $idUsuario;
    }
    $consulta = $conexao->prepare($sql);
    $consulta->execute($valores);
    jsonResposta(['sucesso' => true]);
} catch (PDOException $erro) { erroBanco($erro); }
