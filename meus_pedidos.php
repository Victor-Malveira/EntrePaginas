<?php
require 'funcoes.php'; require 'conexao.php';
if (!isset($_SESSION['usuario_id'])) { header('Location: login.php'); exit; }
$consulta = $pdo->prepare('SELECT * FROM pedidos WHERE usuario_id = ? ORDER BY id DESC');
$consulta->execute([$_SESSION['usuario_id']]); $pedidos = $consulta->fetchAll();
$metodos = ['pix'=>'PIX','cartao'=>'Cartão','boleto'=>'Boleto'];
cabecalho('Meus pedidos'); ?>
<main class="container secao"><h1 class="titulo-pagina">Meus pedidos</h1><div class="painel"><?php if (!$pedidos): ?><p>Você ainda não fez pedidos.</p><?php else: ?><?php foreach ($pedidos as $pedido): ?><div class="resumo-linha"><span><a class="link-pedido" href="pedido.php?id=<?= (int)$pedido['id'] ?>">Pedido #<?= (int)$pedido['id'] ?></a><small><?= h($pedido['criado_em']) ?> · <?= h($metodos[$pedido['forma_pagamento']]) ?> · Simulado</small></span><strong><?= moeda($pedido['total']) ?></strong></div><?php endforeach; ?><?php endif; ?></div></main><?php rodape(); ?>
