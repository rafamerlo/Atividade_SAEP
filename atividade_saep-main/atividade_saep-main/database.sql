-- Estrutura conforme o diagrama do banco.
-- Se você já tem o banco criado, NÃO precisa importar este arquivo:
-- basta apontar DB_NAME em config.php para o seu banco.

CREATE DATABASE IF NOT EXISTS eleitor
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE eleitor;

CREATE TABLE IF NOT EXISTS eleitor1 (
  id_eleitor1   INT          NOT NULL AUTO_INCREMENT,
  nome          VARCHAR(100) NOT NULL,
  numero_titulo VARCHAR(20)  NOT NULL,
  cidade        VARCHAR(80)  NULL,
  PRIMARY KEY (id_eleitor1)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS candidato1 (
  id_candidato1    INT          NOT NULL AUTO_INCREMENT,
  nome             VARCHAR(100) NOT NULL,
  numero_candidato INT          NOT NULL,
  cargo            VARCHAR(50)  NOT NULL,
  partido_ficticio VARCHAR(50)  NULL,
  PRIMARY KEY (id_candidato1)
) ENGINE=InnoDB;

-- Dados de exemplo (fictícios)
INSERT INTO eleitor1 (nome, numero_titulo, cidade) VALUES
  ('Mariana Souza Lima',   'TIT001', 'Santo André'),
  ('Carlos Eduardo Alves', 'TIT002', 'São Bernardo do Campo'),
  ('Beatriz Nogueira',     'TIT003', NULL);

INSERT INTO candidato1 (nome, numero_candidato, cargo, partido_ficticio) VALUES
  ('Helena Prado',    13,    'Prefeito', 'Partido Horizonte'),
  ('Roberto Tavares', 45,    'Prefeito', 'Partido Aurora'),
  ('Ana Clara Mota',  12345, 'Vereador', NULL);
