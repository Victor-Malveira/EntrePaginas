<?php
require 'funcoes.php'; require 'conexao.php';
$tipo = $_GET['tipo'] ?? '';
$id = filter_var($_GET['id'] ?? null,FILTER_VALIDATE_INT);
if (!in_array($tipo,['livro','box'],true) || !$id) {http_response_code(404);exit('Produto não encontrado.');}
$tabela = $tipo === 'box' ? 'boxes' : 'livros';
$stmt = $pdo->prepare("SELECT * FROM $tabela WHERE id = ?");$stmt->execute([$id]);$p = $stmt->fetch();
if (!$p) {http_response_code(404);exit('Produto não encontrado.');}
$nome = $tipo === 'box' ? $p['nome'] : $p['titulo'];
$preco = (float)($tipo === 'box' ? $p['preco_promocional'] : $p['preco']);
eventoAnalytics('view_item',['currency'=>'BRL','value'=>$preco,'items'=>[['item_id'=>($tipo==='box'?'b':'l').$id,'item_name'=>$nome,'item_category'=>$tipo==='box'?'Box':'Livro','price'=>$preco,'quantity'=>1]]]);
cabecalho($nome);
?>
<main class="container secao"><div class="painel"><a href="index.php">← Voltar ao catálogo</a><h1 class="titulo-pagina"><?= h($nome) ?></h1>
<p><?= h($tipo === 'box' ? $p['descricao'] : $p['autor']) ?></p>
<p><strong class="preco"><?= moeda($preco) ?></strong></p><p>Estoque: <?= (int)$p['estoque'] ?></p>
<form action="carrinho.php" method="post"><input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>"><input type="hidden" name="tipo" value="<?= h($tipo) ?>"><input type="hidden" name="produto_id" value="<?= (int)$id ?>"><input type="hidden" name="acao" value="adicionar"><button class="botao" <?= (int)$p['estoque']<1?'disabled':'' ?>>Adicionar ao carrinho</button></form></div></main>
<?php rodape(); ?>
