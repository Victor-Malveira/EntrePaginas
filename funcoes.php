<?php
if (session_status() === PHP_SESSION_NONE) session_start();
if (empty($_SESSION['csrf'])) $_SESSION['csrf'] = bin2hex(random_bytes(32));
function h($texto) { return htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8'); }
function moeda($valor) { return 'R$ ' . number_format((float)$valor, 2, ',', '.'); }
function validarPost() {
 if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) { http_response_code(403); exit('Solicitação inválida.'); }
}
function itensCarrinho($pdo) {
 $itens = [];
 foreach (['livro'=>'livros','box'=>'boxes'] as $tipo=>$tabela) {
  $prefixo = $tipo === 'livro' ? 'l' : 'b';
  $selecionados = [];
  foreach (($_SESSION['carrinho'] ?? []) as $chave=>$qtd) {
   if (preg_match('/^'.$prefixo.'([1-9][0-9]*)$/', (string)$chave, $m)) $selecionados[(int)$m[1]] = (int)$qtd;
  }
  if (!$selecionados) continue;
  $ids = array_keys($selecionados);
  $marcadores = implode(',', array_fill(0, count($ids), '?'));
  $consulta = $pdo->prepare("SELECT * FROM $tabela WHERE id IN ($marcadores)");
  $consulta->execute($ids);
  foreach ($consulta->fetchAll() as $produto) {
   $quantidade = min(max(0, $selecionados[$produto['id']]), (int)$produto['estoque']);
   if ($quantidade < 1) continue;
   $produto['tipo'] = $tipo;
   $produto['chave'] = $prefixo.$produto['id'];
   $produto['titulo'] = $tipo === 'box' ? $produto['nome'] : $produto['titulo'];
   $produto['preco'] = $tipo === 'box' ? $produto['preco_promocional'] : $produto['preco'];
   $produto['quantidade'] = $quantidade;
   $itens[] = $produto;
  }
 }
 return $itens;
}
function eventoAnalytics($nome, $dados) {
 $_SESSION['eventos_analytics'][] = ['nome'=>$nome, 'dados'=>$dados];
}
function dadosAnalytics($itens) {
 return array_map(function($item) {
  return ['item_id'=>$item['chave'], 'item_name'=>$item['titulo'], 'item_category'=>$item['tipo']==='box'?'Box':'Livro', 'price'=>(float)$item['preco'], 'quantity'=>(int)$item['quantidade']];
 }, $itens);
}
function cabecalho($titulo = 'Entre Páginas') {
 $eventos = $_SESSION['eventos_analytics'] ?? [];
 unset($_SESSION['eventos_analytics']); ?>
<!DOCTYPE html><html lang="pt-br"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title><?= h($titulo) ?> | Entre Páginas</title><link rel="stylesheet" href="css/estilo.css"><script async src="https://www.googletagmanager.com/gtag/js?id=G-VD4131DXST"></script>
<script>
window.dataLayer=window.dataLayer||[];
function gtag(){dataLayer.push(arguments);}
gtag('consent','default',{analytics_storage:'denied',ad_storage:'denied',ad_user_data:'denied',ad_personalization:'denied',wait_for_update:500});
gtag('js',new Date());
gtag('config','G-VD4131DXST');
try {if(localStorage.getItem('ep_analytics_consent')==='accepted') gtag('consent','update',{analytics_storage:'granted'});}catch(e){}
window.epEventos=<?= json_encode($eventos, JSON_UNESCAPED_UNICODE|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT) ?>;
</script><script defer src="js/analytics.js"></script></head><body>
<header class="cabecalho"><div class="container barra"><a class="marca" href="index.php">📚 Entre Páginas</a><nav><a href="index.php#livros">Livros</a><a href="index.php#boxes">Boxes</a><a href="carrinho.php">Carrinho (<?= array_sum($_SESSION['carrinho'] ?? []) ?>)</a><?php if (isset($_SESSION['usuario_id'])): ?><span class="saudacao">Olá, <?= h($_SESSION['usuario_nome']) ?>!</span><a href="meus_pedidos.php">Meus pedidos</a><a href="logout.php">Sair</a><?php else: ?><a href="login.php">Entrar</a><a class="link-destaque" href="cadastro.php">Criar conta</a><?php endif; ?></nav></div></header>
<?php }
function rodape() { ?><div id="ep-cookies" class="cookies-aviso" hidden role="dialog" aria-label="Preferências de cookies"><p>Usamos cookies opcionais do Google Analytics para analisar visitas e melhorar a livraria. Você pode aceitar ou recusar. <a href="privacidade.php">Política de privacidade</a>.</p><div><button type="button" id="ep-aceitar" class="botao">Aceitar</button> <button type="button" id="ep-recusar" class="botao botao-secundario">Recusar</button></div></div><footer class="rodape">© <?= date('Y') ?> Entre Páginas · Projeto acadêmico · Pagamentos fictícios · <a href="privacidade.php">Privacidade e cookies</a></footer></body></html><?php }
