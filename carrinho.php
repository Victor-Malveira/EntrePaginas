<?php
require 'funcoes.php'; require 'conexao.php';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 validarPost();
 $acao = $_POST['acao'] ?? '';
 $tipo = $_POST['tipo'] ?? '';
 $id = filter_var($_POST['produto_id'] ?? null, FILTER_VALIDATE_INT);
 if ($id && $id > 0 && in_array($tipo, ['livro','box'], true)) {
  $tabela = $tipo === 'box' ? 'boxes' : 'livros';
  $chave = ($tipo === 'box' ? 'b' : 'l').$id;
  if ($acao === 'remover') unset($_SESSION['carrinho'][$chave]);
  elseif (in_array($acao, ['adicionar','atualizar'], true)) {
   $consulta = $pdo->prepare("SELECT estoque FROM $tabela WHERE id = ?");
   $consulta->execute([$id]); $produto = $consulta->fetch();
   if ($produto) {
    $atual = (int)($_SESSION['carrinho'][$chave] ?? 0);
    $qtd = $acao === 'adicionar' ? $atual + 1 : max(0, min(99, (int)($_POST['quantidade'] ?? 0)));
    $qtd = min($qtd, (int)$produto['estoque']);
    if ($qtd > 0) {
     $_SESSION['carrinho'][$chave] = $qtd;
     if ($acao === 'adicionar' && $qtd > $atual) {
      $preco = $tipo === 'box' ? 'preco_promocional' : 'preco';
      $nome = $tipo === 'box' ? 'nome' : 'titulo';
      $detalhe = $pdo->prepare("SELECT $nome AS titulo, $preco AS preco FROM $tabela WHERE id=?");
      $detalhe->execute([$id]); $d = $detalhe->fetch();
      if ($d) eventoAnalytics('add_to_cart',['currency'=>'BRL','value'=>(float)$d['preco'],'items'=>[['item_id'=>$chave,'item_name'=>$d['titulo'],'item_category'=>$tipo==='box'?'Box':'Livro','price'=>(float)$d['preco'],'quantity'=>1]]]);
     }
    }
    else unset($_SESSION['carrinho'][$chave]);
   }
  }
 }
 header('Location: carrinho.php'); exit;
}
$itens = itensCarrinho($pdo); $total = 0;
cabecalho('Carrinho'); ?>
<main class="container secao"><h1 class="titulo-pagina">Meu carrinho</h1>
<?php if (!$itens): ?><div class="painel"><p>Seu carrinho está vazio.</p><a class="botao" href="index.php#livros">Ver livros</a></div><?php else: ?>
<div class="painel"><div class="tabela-scroll"><table class="tabela"><thead><tr><th>Produto</th><th>Preço</th><th>Quantidade</th><th>Subtotal</th><th></th></tr></thead><tbody>
<?php foreach ($itens as $item): $subtotal = $item['preco'] * $item['quantidade']; $total += $subtotal; ?><tr><td><strong><?= h($item['titulo']) ?></strong><small><?= $item['tipo'] === 'box' ? 'Box promocional' : h($item['autor']) ?></small></td><td><?= moeda($item['preco']) ?></td><td><form class="linha-form" action="carrinho.php" method="post"><input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>"><input type="hidden" name="acao" value="atualizar"><input type="hidden" name="tipo" value="<?= h($item['tipo']) ?>"><input type="hidden" name="produto_id" value="<?= (int)$item['id'] ?>"><input type="number" name="quantidade" min="0" max="<?= (int)$item['estoque'] ?>" value="<?= (int)$item['quantidade'] ?>"><button class="botao botao-pequeno">Atualizar</button></form></td><td><?= moeda($subtotal) ?></td><td><form action="carrinho.php" method="post"><input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>"><input type="hidden" name="acao" value="remover"><input type="hidden" name="tipo" value="<?= h($item['tipo']) ?>"><input type="hidden" name="produto_id" value="<?= (int)$item['id'] ?>"><button class="link-botao">Remover</button></form></td></tr><?php endforeach; ?>
</tbody></table></div><div class="resumo"><strong>Total: <?= moeda($total) ?></strong><a class="botao" href="finalizar.php">Finalizar compra</a></div></div><?php endif; ?></main><?php rodape(); ?>
