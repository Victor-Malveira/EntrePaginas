<?php
require 'funcoes.php'; require 'conexao.php';
$livros = $pdo->query('SELECT * FROM livros ORDER BY id')->fetchAll();
$boxes = $pdo->query('SELECT * FROM boxes ORDER BY id')->fetchAll();
cabecalho('Livraria');
?>
<main><section class="hero"><div class="container"><span class="sobretitulo">SUA PRÓXIMA HISTÓRIA COMEÇA AQUI</span><h1>Histórias que merecem<br>um lugar na sua estante.</h1><p>Explore mundos imaginários e encontre sua próxima leitura favorita.</p><a class="botao" href="#livros">Explorar livros</a></div></section>
<section class="container secao" id="livros"><div class="titulo-secao"><div><span class="sobretitulo">NOSSA SELEÇÃO</span><h2>Livros em destaque</h2></div><span class="contador"><?= count($livros) ?> títulos</span></div><div class="grade-livros">
<?php foreach ($livros as $livro): ?><article class="card-livro"><div class="capa" style="--cor-capa: <?= h($livro['cor']) ?>"><span class="ornamento"><?= h($livro['simbolo']) ?></span><strong><?= h($livro['titulo']) ?></strong><small><?= h($livro['autor']) ?></small></div><div class="dados-livro"><h3><a class="link-produto" href="produto.php?tipo=livro&id=<?= (int)$livro['id'] ?>"><?= h($livro['titulo']) ?></a></h3><p><?= h($livro['autor']) ?></p><strong class="preco"><?= moeda($livro['preco']) ?></strong><p>Estoque: <?= (int)$livro['estoque'] ?></p><form action="carrinho.php" method="post"><input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>"><input type="hidden" name="tipo" value="livro"><input type="hidden" name="produto_id" value="<?= (int)$livro['id'] ?>"><input type="hidden" name="acao" value="adicionar"><button class="botao botao-card" <?= $livro['estoque'] < 1 ? 'disabled' : '' ?>>Adicionar ao carrinho</button></form></div></article><?php endforeach; ?>
</div></section>
<section class="container secao" id="boxes"><div class="titulo-secao"><div><span class="sobretitulo">SAGAS COMPLETAS COM DESCONTO</span><h2>Boxes de livros</h2></div><span class="contador"><?= count($boxes) ?> boxes</span></div>
<div class="grade-livros">
<?php foreach ($boxes as $box): $desconto = $box['preco_original'] > 0 ? round((1 - $box['preco_promocional'] / $box['preco_original']) * 100) : 0; ?>
<article class="card-livro"><div class="capa capa-box" style="--cor-capa: <?= h($box['cor']) ?>"><span class="ornamento"><?= h($box['simbolo']) ?></span><strong><?= h($box['nome']) ?></strong><small>COLEÇÃO COMPLETA</small></div>
<div class="dados-livro"><h3><a class="link-produto" href="produto.php?tipo=box&id=<?= (int)$box['id'] ?>"><?= h($box['nome']) ?></a></h3><p><?= h($box['descricao']) ?></p><div class="precos-box"><del><?= moeda($box['preco_original']) ?></del><strong class="preco"><?= moeda($box['preco_promocional']) ?></strong><span class="desconto">-<?= (int)$desconto ?>%</span></div><p>Estoque: <?= (int)$box['estoque'] ?></p>
<form action="carrinho.php" method="post"><input type="hidden" name="csrf" value="<?= h($_SESSION['csrf']) ?>"><input type="hidden" name="tipo" value="box"><input type="hidden" name="produto_id" value="<?= (int)$box['id'] ?>"><input type="hidden" name="acao" value="adicionar"><button class="botao botao-card" <?= $box['estoque'] < 1 ? 'disabled' : '' ?>>Adicionar box ao carrinho</button></form></div></article>
<?php endforeach; ?>
</div></section></main><?php rodape(); ?>
