<?php
require 'funcoes.php'; require 'conexao.php';
if (!isset($_SESSION['usuario_id'])) { header('Location: login.php?voltar=finalizar'); exit; }
$itens = itensCarrinho($pdo);
if (!$itens) { header('Location: carrinho.php'); exit; }
$total = array_sum(array_map(fn($item) => $item['preco'] * $item['quantidade'], $itens));
$erro = '';
if ($_SERVER['REQUEST_METHOD'] === 'GET') eventoAnalytics('begin_checkout',['currency'=>'BRL','value'=>(float)$total,'items'=>dadosAnalytics($itens)]);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 validarPost();
 $metodo = $_POST['pagamento'] ?? '';
 if (!in_array($metodo, ['pix','cartao','boleto'], true)) $erro = 'Selecione uma forma de pagamento.';
 else {
  try {
   $pdo->beginTransaction();
   $atuais = [];
   foreach (['livro'=>'livros','box'=>'boxes'] as $tipo=>$tabela) {
    $ids = array_column(array_values(array_filter($itens, fn($i) => $i['tipo'] === $tipo)), 'id');
    if (!$ids) continue;
    $marcadores = implode(',', array_fill(0, count($ids), '?'));
    $consulta = $pdo->prepare("SELECT * FROM $tabela WHERE id IN ($marcadores) ORDER BY id FOR UPDATE");
    $consulta->execute($ids);
    foreach ($consulta->fetchAll() as $produto) $atuais[($tipo === 'box' ? 'b' : 'l').$produto['id']] = $produto;
   }
   $total = 0;
   foreach ($itens as $item) {
    $chave = $item['chave']; $quantidade = (int)$item['quantidade'];
    if (!isset($atuais[$chave]) || $atuais[$chave]['estoque'] < $quantidade || (int)($_SESSION['carrinho'][$chave] ?? 0) !== $quantidade) throw new RuntimeException('Estoque alterado. Confira seu carrinho.');
    $preco = $item['tipo'] === 'box' ? $atuais[$chave]['preco_promocional'] : $atuais[$chave]['preco'];
    $total += (float)$preco * $quantidade;
   }
   $pedido = $pdo->prepare('INSERT INTO pedidos (usuario_id,total,forma_pagamento,status) VALUES (?,?,?,?)');
   $pedido->execute([$_SESSION['usuario_id'], number_format($total,2,'.',''), $metodo, 'simulado']);
   $pedidoId = (int)$pdo->lastInsertId();
   $salvarItem = $pdo->prepare('INSERT INTO itens_pedido (pedido_id,livro_id,box_id,quantidade,preco_unitario) VALUES (?,?,?,?,?)');
   foreach ($itens as $item) {
    $tipo = $item['tipo']; $produto = $atuais[$item['chave']];
    $preco = $tipo === 'box' ? $produto['preco_promocional'] : $produto['preco'];
    $salvarItem->execute([$pedidoId, $tipo === 'livro' ? $item['id'] : null, $tipo === 'box' ? $item['id'] : null, $item['quantidade'], $preco]);
    $tabela = $tipo === 'box' ? 'boxes' : 'livros';
    $baixar = $pdo->prepare("UPDATE $tabela SET estoque = estoque - ? WHERE id = ?");
    $baixar->execute([$item['quantidade'],$item['id']]);
   }
   $pdo->commit(); $_SESSION['carrinho'] = [];
   $_SESSION['analytics_compra_'.$pedidoId] = ['transaction_id'=>(string)$pedidoId,'currency'=>'BRL','value'=>(float)$total,'payment_type'=>$metodo,'items'=>dadosAnalytics($itens)];
   header('Location: pedido.php?id='.$pedidoId); exit;
  } catch (Throwable $e) {
   if ($pdo->inTransaction()) $pdo->rollBack();
   $erro = $e instanceof RuntimeException ? $e->getMessage() : 'Não foi possível registrar o pedido.';
  }
 }
}
cabecalho('Finalizar compra'); ?>
<main class="container secao"><h1 class="titulo-pagina">Finalizar compra</h1><div class="checkout-grid"><div class="painel"><h2>Escolha o pagamento</h2><p class="aviso">Demonstração acadêmica: nenhuma cobrança será realizada. Não digite dados reais de cartão.</p>
<?php if ($erro): ?><p class="erro"><?= h($erro) ?></p><?php endif; ?>
<form method="post"><input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>"><label class="opcao-pagamento"><input type="radio" name="pagamento" value="pix" required><span><strong>PIX</strong><small>Pagamento fictício, sem QR Code bancário</small></span></label><label class="opcao-pagamento"><input type="radio" name="pagamento" value="cartao"><span><strong>Cartão de crédito</strong><small>Simulação sem solicitar número ou CVV</small></span></label><label class="opcao-pagamento"><input type="radio" name="pagamento" value="boleto"><span><strong>Boleto</strong><small>Comprovante demonstrativo, sem linha digitável real</small></span></label><button class="botao botao-largo">Confirmar pedido simulado</button></form></div>
<aside class="painel"><h2>Resumo do pedido</h2><?php foreach ($itens as $item): ?><div class="resumo-linha"><span><?= (int)$item['quantidade'] ?> × <?= h($item['titulo']) ?></span><strong><?= moeda($item['preco'] * $item['quantidade']) ?></strong></div><?php endforeach; ?><div class="resumo-linha total"><span>Total</span><strong><?= moeda($total) ?></strong></div></aside></div></main><?php rodape(); ?>
