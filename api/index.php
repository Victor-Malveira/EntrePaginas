<?php
// Ponto de entrada PHP para a Vercel. Nunca serve arquivos PHP como texto.
$path = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
$path = trim((string)$path, '/');
if ($path === '') $path = 'index.php';
if (!preg_match('/^[a-z_]+\.php$/i', $path)) {
    http_response_code(404);
    exit('Página não encontrada.');
}
$publicas = ['index.php','cadastro.php','carrinho.php','finalizar.php','login.php','logout.php','meus_pedidos.php','pedido.php','produto.php','privacidade.php'];
if (!in_array($path, $publicas, true)) {
    http_response_code(404);
    exit('Página não encontrada.');
}
chdir(dirname(__DIR__));
require dirname(__DIR__) . '/' . $path;
