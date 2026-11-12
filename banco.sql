-- Entre Páginas — instalação inicial no Aiven MySQL 8.4
-- Selecione o banco defaultdb antes de importar.
USE defaultdb;

CREATE TABLE IF NOT EXISTS usuarios (
 id INT AUTO_INCREMENT PRIMARY KEY,
 nome VARCHAR(120) NOT NULL,
 email VARCHAR(190) NOT NULL UNIQUE,
 senha VARCHAR(255) NOT NULL,
 criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS livros (
 id INT AUTO_INCREMENT PRIMARY KEY,
 titulo VARCHAR(180) NOT NULL,
 autor VARCHAR(120) NOT NULL,
 preco DECIMAL(10,2) NOT NULL,
 estoque INT NOT NULL DEFAULT 0,
 cor VARCHAR(7) NOT NULL DEFAULT '#526d82',
 simbolo VARCHAR(10) NOT NULL DEFAULT '✦'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS boxes (
 id INT AUTO_INCREMENT PRIMARY KEY,
 nome VARCHAR(180) NOT NULL,
 descricao TEXT NOT NULL,
 preco_original DECIMAL(10,2) NOT NULL,
 preco_promocional DECIMAL(10,2) NOT NULL,
 estoque INT NOT NULL DEFAULT 0,
 cor VARCHAR(7) NOT NULL DEFAULT '#495e80',
 simbolo VARCHAR(10) NOT NULL DEFAULT '✦'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS box_livros (
 box_id INT NOT NULL,
 livro_id INT NOT NULL,
 PRIMARY KEY (box_id, livro_id),
 FOREIGN KEY (box_id) REFERENCES boxes(id) ON DELETE CASCADE,
 FOREIGN KEY (livro_id) REFERENCES livros(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS pedidos (
 id INT AUTO_INCREMENT PRIMARY KEY,
 usuario_id INT NOT NULL,
 total DECIMAL(10,2) NOT NULL,
 forma_pagamento ENUM('pix','cartao','boleto') NOT NULL,
 status VARCHAR(30) NOT NULL DEFAULT 'simulado',
 criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS itens_pedido (
 id INT AUTO_INCREMENT PRIMARY KEY,
 pedido_id INT NOT NULL,
 livro_id INT NULL,
 box_id INT NULL,
 quantidade INT NOT NULL,
 preco_unitario DECIMAL(10,2) NOT NULL,
 FOREIGN KEY (pedido_id) REFERENCES pedidos(id),
 FOREIGN KEY (livro_id) REFERENCES livros(id),
 FOREIGN KEY (box_id) REFERENCES boxes(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO livros (id,titulo,autor,preco,estoque,cor,simbolo) VALUES
(1,'O Jardim das Estrelas','Helena Duarte',49.90,20,'#526d82','✦'),
(2,'Além do Horizonte','Lucas Valença',59.90,15,'#a46a55','☀'),
(3,'A Última Biblioteca','Marina Azevedo',44.90,18,'#5b7463','❧'),
(4,'Segredos da Neblina','Rafael Monteiro',54.90,12,'#735b83','☾'),
(5,'Cartas para o Amanhã','Clara Fontes',39.90,25,'#b78078','✉'),
(6,'O Reino de Âmbar','Pedro Alencar',64.90,10,'#9c793d','♛')
ON DUPLICATE KEY UPDATE titulo=VALUES(titulo), autor=VALUES(autor), preco=VALUES(preco), cor=VALUES(cor), simbolo=VALUES(simbolo);

INSERT INTO boxes (id,nome,descricao,preco_original,preco_promocional,estoque,cor,simbolo)
VALUES (1,'Box Crônicas de Âmbar','Trilogia fictícia completa em edição especial.',179.70,139.90,8,'#654b7b','✦')
ON DUPLICATE KEY UPDATE nome=VALUES(nome),descricao=VALUES(descricao),preco_original=VALUES(preco_original),preco_promocional=VALUES(preco_promocional),cor=VALUES(cor),simbolo=VALUES(simbolo);

-- Catálogo de fantasia e boxes (preços ilustrativos do projeto).
-- IDs novos preservam os produtos de pedidos de teste anteriores.
INSERT INTO livros (id,titulo,autor,preco,estoque,cor,simbolo) VALUES
(7,'A Guerra dos Tronos','George R. R. Martin',69.90,20,'#343f51','♛'),
(8,'A Fúria dos Reis','George R. R. Martin',74.90,20,'#6e3535','♛'),
(9,'A Tormenta de Espadas','George R. R. Martin',79.90,20,'#4d5369','♛'),
(10,'O Festim dos Corvos','George R. R. Martin',74.90,20,'#55505d','♛'),
(11,'A Dança dos Dragões','George R. R. Martin',84.90,20,'#6e5542','♛'),
(12,'Harry Potter e a Pedra Filosofal','J. K. Rowling',54.90,20,'#75512e','✦'),
(13,'Harry Potter e a Câmara Secreta','J. K. Rowling',54.90,20,'#72543c','✦'),
(14,'Harry Potter e o Prisioneiro de Azkaban','J. K. Rowling',59.90,20,'#536075','✦'),
(15,'Harry Potter e o Cálice de Fogo','J. K. Rowling',64.90,20,'#764b3d','✦'),
(16,'Harry Potter e a Ordem da Fênix','J. K. Rowling',69.90,20,'#65556b','✦'),
(17,'Harry Potter e o Enigma do Príncipe','J. K. Rowling',64.90,20,'#526453','✦'),
(18,'Harry Potter e as Relíquias da Morte','J. K. Rowling',69.90,20,'#494d5d','✦'),
(19,'O Último Desejo','Andrzej Sapkowski',59.90,20,'#505d59','⚔'),
(20,'O Ladrão de Raios','Rick Riordan',49.90,20,'#376b8d','⚡'),
(21,'O Império Final','Brandon Sanderson',79.90,20,'#39434a','✦')
ON DUPLICATE KEY UPDATE titulo=VALUES(titulo),autor=VALUES(autor),preco=VALUES(preco),cor=VALUES(cor),simbolo=VALUES(simbolo);

INSERT INTO boxes (id,nome,descricao,preco_original,preco_promocional,estoque,cor,simbolo) VALUES
(2,'Box As Crônicas de Gelo e Fogo','Coleção com os cinco romances publicados de As Crônicas de Gelo e Fogo, de George R. R. Martin.',384.50,319.90,10,'#343f51','♛'),
(3,'Box Harry Potter','Coleção completa dos sete romances de Harry Potter, de J. K. Rowling.',439.30,359.90,10,'#75512e','✦')
ON DUPLICATE KEY UPDATE nome=VALUES(nome),descricao=VALUES(descricao),preco_original=VALUES(preco_original),preco_promocional=VALUES(preco_promocional),cor=VALUES(cor),simbolo=VALUES(simbolo);

INSERT IGNORE INTO box_livros (box_id,livro_id) VALUES
(2,7),(2,8),(2,9),(2,10),(2,11),
(3,12),(3,13),(3,14),(3,15),(3,16),(3,17),(3,18);
