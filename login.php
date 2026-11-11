<?php
session_start();
require 'conexao.php';
if (isset($_SESSION['usuario_id'])) { header('Location: index.php'); exit; }
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = strtolower(trim($_POST['email'] ?? ''));
    $senha = $_POST['senha'] ?? '';
    $consulta = $pdo->prepare('SELECT id, nome, senha FROM usuarios WHERE email = ?');
    $consulta->execute([$email]);
    $usuario = $consulta->fetch();
    if ($usuario && password_verify($senha, $usuario['senha'])) {
        session_regenerate_id(true);
        $_SESSION['usuario_id'] = (int)$usuario['id'];
        $_SESSION['usuario_nome'] = $usuario['nome'];
        header('Location: ' . (($_POST['voltar'] ?? '') === 'finalizar' ? 'finalizar.php' : 'index.php')); exit;
    }
    $erro = 'E-mail ou senha incorretos.';
}
?>
<!DOCTYPE html><html lang="pt-br"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Entrar | Entre Páginas</title><link rel="stylesheet" href="css/estilo.css"></head><body>
<header class="cabecalho"><div class="container barra"><a class="marca" href="index.php">📚 Entre Páginas</a><nav><a href="index.php">Início</a><a href="cadastro.php">Criar conta</a></nav></div></header>
<main class="area-form"><form class="formulario" action="login.php" method="post"><h1>Bem-vindo de volta</h1><p>Entre na sua conta para continuar.</p>
<?php if ($erro): ?><div class="erro" role="alert"><?= htmlspecialchars($erro, ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
<input type="hidden" name="voltar" value="<?= ($_GET['voltar'] ?? '') === 'finalizar' ? 'finalizar' : '' ?>"><label for="email">E-mail</label><input id="email" type="email" name="email" required value="<?= htmlspecialchars($email ?? '', ENT_QUOTES, 'UTF-8') ?>">
<label for="senha">Senha</label><input id="senha" type="password" name="senha" required><button class="botao" type="submit">Entrar</button><p class="rodape-form">Ainda não tem conta? <a href="cadastro.php">Cadastre-se</a></p></form></main></body></html>
