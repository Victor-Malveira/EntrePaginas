<?php
// Configuração por variáveis de ambiente para Vercel/Aiven.
// Em desenvolvimento local, mantenha os valores de fallback do XAMPP.
$host = getenv('DB_HOST') ?: 'localhost';
$porta = getenv('DB_PORT') ?: '3306';
$banco = getenv('DB_NAME') ?: 'livraria';
$usuario = getenv('DB_USER') ?: 'root';
$senha = getenv('DB_PASSWORD') ?: '';

try {
    $opcoes = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::ATTR_TIMEOUT => 10,
    ];
    // Aiven exige TLS. Informe DB_SSL_CA com o caminho de um certificado CA,
    // quando disponível, para verificar a identidade do servidor.
    $ca = getenv('DB_SSL_CA');
    if ($ca) {
        $opcoes[PDO::MYSQL_ATTR_SSL_CA] = $ca;
        $opcoes[PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT] = true;
    }
    $pdo = new PDO(
        "mysql:host=$host;port=$porta;dbname=$banco;charset=utf8mb4",
        $usuario,
        $senha,
        $opcoes
    );
} catch (PDOException $e) {
    error_log('Erro de conexão MySQL: ' . $e->getMessage());
    http_response_code(500);
    exit('Não foi possível conectar ao banco de dados.');
}
