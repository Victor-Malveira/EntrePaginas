<?php
session_start();
require 'conexao.php';
if (isset($_SESSION['usuario_id'])) { header('Location: index.php'); exit; }
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = strtolower(trim($_POST['email'] ?? ''));
    $senha = $_POST['senha'] ?? '';
    $confirmacao = $_POST['confirmacao'] ?? '';
    if (mb_strlen($nome) < 2 || mb_strlen($nome) > 120) $erro = 'Informe um nome válido.';
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 190) $erro = 'Informe um e-mail válido.';
    elseif (strlen($senha) < 8) $erro = 'A senha deve ter pelo menos 8 caracteres.';
    elseif ($senha !== $confirmacao) $erro = 'As senhas não coincidem.';
    else {
        $consulta = $pdo->prepare('SELECT id FROM usuarios WHERE email = ?');
        $consulta->execute([$email]);
        if ($consulta->fetch()) $erro = 'Este e-mail já está cadastrado.';
        else {
            try {
                $salvar = $pdo->prepare('INSERT INTO usuarios (nome, email, senha) VALUES (?, ?, ?)');
                $salvar->execute([$nome, $email, password_hash($senha, PASSWORD_DEFAULT)]);
                session_regenerate_id(true);
                $_SESSION['usuario_id'] = (int)$pdo->lastInsertId();
                $_SESSION['usuario_nome'] = $nome;
                header('Location: index.php'); exit;
            } catch (PDOException $e) {
                if ($e->getCode() === '23000') $erro = 'Este e-mail já está cadastrado.';
                else $erro = 'Não foi possível concluir o cadastro.';
            }
        }
    }
}
?>
<!DOCTYPE html><html lang="pt-br"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Criar conta | Entre Páginas</title><link rel="stylesheet" href="css/estilo.css"></head><body>
<header class="cabecalho"><div class="container barra"><a class="marca" href="index.php">📚 Entre Páginas</a><nav><a href="index.php">Início</a><a href="login.php">Entrar</a></nav></div></header>
<main class="area-form"><form class="formulario" action="cadastro.php" method="post"><h1>Criar conta</h1><p>Cadastre-se para explorar nossa livraria.</p>
<?php if ($erro): ?><div class="erro" role="alert"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<label for="nome">Nome completo</label><input id="nome" name="nome" maxlength="120" required value="<?= htmlspecialchars($nome ?? '', ENT_QUOTES, 'UTF-8') ?>">
<label for="email">E-mail</label><input id="email" type="email" name="email" maxlength="190" required value="<?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8') ?>">
<label for="senha">Senha (mínimo de 8 caracteres)</label><input id="senha" type="password" name="senha" minlength="8" required>
<label for="confirmacao">Confirmar senha</label><input id="confirmacao" type="password" name="confirmacao" minlength="8" required>
<button class="botao" type="submit">Cadastrar</button><p class="rodape-form">Já tem conta? <a href="login.php">Faça login</a></p></form></main></body></html>
