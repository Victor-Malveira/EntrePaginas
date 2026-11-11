USE livraria;
CREATE TABLE IF NOT EXISTS boxes (
 id INT AUTO_INCREMENT PRIMARY KEY,
 nome VARCHAR(180) NOT NULL,
 descricao TEXT NOT NULL,
 preco_original DECIMAL(10,2) NOT NULL,
 preco_promocional DECIMAL(10,2) NOT NULL,
 estoque INT NOT NULL DEFAULT 0,
 cor VARCHAR(7) NOT NULL DEFAULT '#495e80',
 simbolo VARCHAR(10) NOT NULL DEFAULT '✦',
 CHECK (preco_original > 0),
 CHECK (preco_promocional >= 0),
 CHECK (preco_promocional <= preco_original),
 CHECK (estoque >= 0)
);
CREATE TABLE IF NOT EXISTS box_livros (
 box_id INT NOT NULL,
 livro_id INT NOT NULL,
 PRIMARY KEY (box_id, livro_id),
 FOREIGN KEY (box_id) REFERENCES boxes(id) ON DELETE CASCADE,
 FOREIGN KEY (livro_id) REFERENCES livros(id)
);
ALTER TABLE itens_pedido MODIFY livro_id INT NULL;
ALTER TABLE itens_pedido ADD COLUMN box_id INT NULL;
ALTER TABLE itens_pedido ADD CONSTRAINT fk_itens_box FOREIGN KEY (box_id) REFERENCES boxes(id);
INSERT INTO boxes (id,nome,descricao,preco_original,preco_promocional,estoque,cor,simbolo)
VALUES (1,'Box Crônicas de Âmbar','Trilogia fictícia completa em edição especial.',179.70,139.90,8,'#654b7b','✦')
ON DUPLICATE KEY UPDATE nome=VALUES(nome),descricao=VALUES(descricao),preco_original=VALUES(preco_original),preco_promocional=VALUES(preco_promocional),cor=VALUES(cor),simbolo=VALUES(simbolo);
