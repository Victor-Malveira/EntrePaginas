<?php
require 'funcoes.php'; require 'conexao.php';
if (!isset($_SESSION['usuario_id'])) { header('Location: login.php'); exit; }
$id = filter_var($_GET['id'] ?? null, FILTER_VALIDATE_INT);
if (!$id) { http_response_code(404); exit('Pedido não encontrado.'); }
$consulta = $pdo->prepare('SELECT * FROM pedidos WHERE id = ? AND usuario_id = ?');
$consulta->execute([$id, $_SESSION['usuario_id']]); $pedido = $consulta->fetch();
if (!$pedido) { http_response_code(404); exit('Pedido não encontrado.'); }
$consulta = $pdo->prepare('SELECT i.*, COALESCE(l.titulo, b.nome) AS titulo FROM itens_pedido i LEFT JOIN livros l ON l.id = i.livro_id LEFT JOIN boxes b ON b.id = i.box_id WHERE i.pedido_id = ?');
$consulta->execute([$id]); $itens = $consulta->fetchAll();
if (isset($_SESSION['analytics_compra_'.$id])) {
 eventoAnalytics('purchase', $_SESSION['analytics_compra_'.$id]);
 unset($_SESSION['analytics_compra_'.$id]);
}
$metodos = ['pix'=>'PIX','cartao'=>'Cartão de crédito','boleto'=>'Boleto'];
cabecalho('Pedido confirmado'); ?>
<main class="container secao"><div class="painel painel-central"><span class="sobretitulo">PEDIDO REGISTRADO</span><h1 class="titulo-pagina">Compra simulada concluída!</h1><p>Pedido <strong>#<?= (int)$pedido['id'] ?></strong> · <?= h($metodos[$pedido['forma_pagamento']]) ?> · <?= h($pedido['criado_em']) ?></p><p class="aviso">Este é um ambiente de demonstração. Nenhum pagamento foi processado e nenhum livro será enviado.</p>
<?php if ($pedido['forma_pagamento'] === 'pix'): ?><div class="recibo">PIX SIMULADO<br><small>Não há chave PIX ou QR Code real.</small></div><?php elseif ($pedido['forma_pagamento'] === 'boleto'): ?><div class="recibo">BOLETO DE DEMONSTRAÇÃO<br><small>Sem código de barras ou valor a pagar.</small></div><?php else: ?><div class="recibo">CARTÃO SIMULADO<br><small>Transação fictícia, sem dados bancários.</small></div><?php endif; ?>
<?php foreach ($itens as $item): ?><div class="resumo-linha"><span><?= (int)$item['quantidade'] ?> × <?= h($item['titulo']) ?></span><strong><?= moeda($item['preco_unitario'] * $item['quantidade']) ?></strong></div><?php endforeach; ?><div class="resumo-linha total"><span>Total</span><strong><?= moeda($pedido['total']) ?></strong></div><p><a class="botao" href="index.php">Voltar à loja</a> <a class="botao botao-secundario" href="meus_pedidos.php">Meus pedidos</a></p></div></main><?php rodape(); ?>
