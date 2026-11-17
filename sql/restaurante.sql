-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 17/11/2026 às 18:11
-- Versão do servidor: 10.4.32-MariaDB
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `restaurante`
--
CREATE DATABASE IF NOT EXISTS `restaurante` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `restaurante`;

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_desperdicio`
--

CREATE TABLE `tb_desperdicio` (
  `id_desperdicio` int(11) NOT NULL,
  `id_ingrediente` int(11) NOT NULL,
  `qtd_desperdicada` decimal(10,3) NOT NULL,
  `data_registro` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `motivo` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_desperdicio`
--

INSERT INTO `tb_desperdicio` (`id_desperdicio`, `id_ingrediente`, `qtd_desperdicada`, `data_registro`, `motivo`) VALUES
(1, 7, 1.500, '2026-11-12 00:50:00', 'Queimou no preparo'),
(2, 10, 3.500, '2026-10-19 02:05:00', 'Óleo da fritadeira descartado após uso'),
(3, 10, 4.000, '2026-11-05 02:00:00', 'Óleo da fritadeira descartado após uso'),
(4, 17, 0.900, '2026-10-16 18:30:00', 'Carne descongelada além do prazo'),
(5, 20, 1.200, '2026-11-03 17:40:00', 'Embalagem violada, descartado'),
(6, 21, 0.600, '2026-11-09 17:50:00', 'Passou da validade'),
(7, 22, 5.000, '2026-10-30 00:30:00', 'Descongelado e não utilizado'),
(8, 23, 6.000, '2026-10-20 17:20:00', 'Mofo no pacote'),
(9, 24, 14.000, '2026-11-06 18:20:00', 'Vencido'),
(10, 33, 2.000, '2026-10-26 01:50:00', 'Queda de energia: descongelou'),
(11, 34, 4.000, '2026-10-15 01:40:00', 'Folhas murchas, não aproveitáveis'),
(12, 35, 1.800, '2026-10-13 18:10:00', 'Passou do ponto de maturação'),
(13, 35, 2.200, '2026-11-12 18:05:00', 'Passou do ponto de maturação'),
(14, 36, 1.500, '2026-11-01 18:00:00', 'Brotando e com mofo'),
(15, 37, 1.200, '2026-10-22 19:00:00', 'Estragou na geladeira'),
(16, 40, 4.000, '2026-11-07 19:10:00', 'Murcho'),
(17, 41, 18.000, '2026-10-27 18:15:00', 'Passaram do ponto, sem uso para suco'),
(18, 43, 24.000, '2026-11-14 01:15:00', 'Ovos quebrados no transporte'),
(19, 47, 2.000, '2026-11-09 01:30:00', 'Garrafa quebrada no estoque'),
(20, 51, 2.000, '2026-11-10 18:45:00', 'Vencido');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_estoque`
--

CREATE TABLE `tb_estoque` (
  `id_ingrediente` int(11) NOT NULL,
  `id_gestor` int(11) NOT NULL,
  `nome_ingredientes` varchar(30) NOT NULL,
  `quantidade_atual` decimal(10,3) NOT NULL DEFAULT 0.000,
  `quantidade_minima` decimal(10,3) NOT NULL DEFAULT 0.000,
  `quantidade_maxima` decimal(10,3) NOT NULL DEFAULT 0.000,
  `unidade_medida` varchar(10) NOT NULL DEFAULT 'un',
  `custo_unitario` decimal(10,2) NOT NULL DEFAULT 0.00,
  `tempo_reposicao_dias` int(11) NOT NULL DEFAULT 2,
  `lote_reposicao` decimal(10,3) NOT NULL DEFAULT 0.000
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_estoque`
--

INSERT INTO `tb_estoque` (`id_ingrediente`, `id_gestor`, `nome_ingredientes`, `quantidade_atual`, `quantidade_minima`, `quantidade_maxima`, `unidade_medida`, `custo_unitario`, `tempo_reposicao_dias`, `lote_reposicao`) VALUES
(7, 5, 'Arroz branco', 25.000, 1.000, 53.000, 'kg', 5.20, 5, 52.000),
(8, 5, 'Feijão carioca', 20.000, 0.200, 44.200, 'kg', 7.80, 5, 44.000),
(9, 5, 'Macarrão espaguete', 12.000, 0.100, 26.100, 'kg', 6.50, 5, 26.000),
(10, 5, 'Óleo de soja', 18.000, 1.000, 35.000, 'l', 7.90, 5, 34.000),
(11, 5, 'Azeite de oliva', 3.000, 0.100, 7.100, 'l', 38.00, 5, 7.000),
(12, 5, 'Vinagre', 4.000, 0.100, 9.100, 'l', 4.50, 5, 9.000),
(13, 5, 'Sal refinado', 10.000, 0.100, 22.100, 'kg', 2.80, 5, 22.000),
(14, 5, 'Sazon', 2000.000, 1.000, 5501.000, 'g', 0.04, 5, 5500.000),
(15, 5, 'Pimenta-do-reino', 800.000, 4.000, 2304.000, 'g', 0.06, 5, 2300.000),
(16, 5, 'Páprica', 600.000, 1.000, 1851.000, 'g', 0.05, 5, 1850.000),
(17, 5, 'Coxão mole (cubos)', 15.000, 0.300, 35.300, 'kg', 38.00, 2, 35.000),
(18, 5, 'Filé mignon', 8.000, 3.000, 20.000, 'kg', 69.90, 2, 17.000),
(19, 5, 'Peito de frango (cubos)', 12.000, 0.100, 26.100, 'kg', 18.90, 2, 26.000),
(20, 5, 'Filé de frango empanado', 14.000, 0.300, 30.300, 'kg', 24.90, 2, 30.000),
(21, 5, 'Bacon fatiado', 6.000, 0.100, 13.100, 'kg', 36.00, 2, 13.000),
(22, 5, 'Hambúrguer bovino 150g', 60.000, 2.000, 182.000, 'un', 3.90, 3, 180.000),
(23, 5, 'Pão de hambúrguer', 80.000, 2.000, 172.000, 'un', 1.20, 2, 170.000),
(24, 5, 'Queijo cheddar (fatia)', 120.000, 2.000, 362.000, 'un', 0.95, 2, 360.000),
(25, 5, 'Queijo mussarela (fatia)', 100.000, 3.000, 273.000, 'un', 0.85, 2, 270.000),
(26, 5, 'Maionese', 4.000, 0.100, 11.100, 'kg', 14.00, 4, 11.000),
(27, 5, 'Picles', 2.000, 0.100, 5.600, 'kg', 22.00, 4, 5.500),
(28, 5, 'Manteiga', 3.000, 0.100, 9.100, 'kg', 38.00, 3, 9.000),
(29, 5, 'Creme de leite', 6.000, 0.100, 18.100, 'l', 22.00, 3, 18.000),
(30, 5, 'Mostarda', 3000.000, 7.000, 7507.000, 'ml', 0.02, 4, 7500.000),
(31, 5, 'Ketchup', 5000.000, 13.000, 11013.000, 'ml', 0.02, 4, 11000.000),
(32, 5, 'Extrato de tomate', 6000.000, 146.000, 13646.000, 'g', 0.01, 5, 13500.000),
(33, 5, 'Batata pré-frita congelada', 30.000, 0.500, 70.500, 'kg', 12.50, 3, 70.000),
(34, 5, 'Alface', 20.000, 1.000, 43.000, 'un', 3.50, 1, 42.000),
(35, 5, 'Tomate', 15.000, 0.200, 35.200, 'kg', 7.50, 1, 35.000),
(36, 5, 'Cebola', 12.000, 0.100, 26.100, 'kg', 5.20, 1, 26.000),
(37, 5, 'Pepino', 6.000, 0.100, 13.100, 'kg', 5.00, 1, 13.000),
(38, 5, 'Cenoura', 8.000, 0.100, 17.100, 'kg', 4.80, 1, 17.000),
(39, 5, 'Alho', 3.000, 0.100, 7.100, 'kg', 30.00, 1, 7.000),
(40, 5, 'Cheiro-verde (maço)', 15.000, 1.000, 36.000, 'un', 2.50, 1, 35.000),
(41, 5, 'Laranja', 150.000, 1.000, 451.000, 'un', 0.45, 1, 450.000),
(42, 5, 'Gelo', 40.000, 0.100, 90.100, 'kg', 2.00, 1, 90.000),
(43, 5, 'Ovo de codorna', 600.000, 3.000, 1803.000, 'un', 0.20, 2, 1800.000),
(44, 5, 'Pão de alho', 80.000, 2.000, 172.000, 'un', 0.90, 3, 170.000),
(45, 5, 'Coca-Cola 2L', 24.000, 1.000, 73.000, 'un', 8.40, 3, 72.000),
(46, 5, 'Coca-Cola 1L', 24.000, 1.000, 53.000, 'un', 4.90, 3, 52.000),
(47, 5, 'Coca-Cola 600ml', 36.000, 1.000, 89.000, 'un', 3.60, 3, 88.000),
(48, 5, 'Coca-Cola lata 350ml', 48.000, 10.000, 186.000, 'un', 2.90, 30, 176.000),
(49, 5, 'Fanta Laranja lata 350ml', 36.000, 1.000, 109.000, 'un', 2.70, 3, 108.000),
(50, 5, 'Guaraná Antarctica 2L', 24.000, 1.000, 73.000, 'un', 5.20, 3, 72.000),
(51, 5, 'Suco Prat\'s Laranja 900ml', 12.000, 1.000, 37.000, 'un', 7.20, 3, 36.000);

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_fornecedores`
--

CREATE TABLE `tb_fornecedores` (
  `id_gestor` int(11) NOT NULL,
  `id_fornecedor` int(11) NOT NULL,
  `nome_fornecedor` text NOT NULL,
  `produto_fornecido` text NOT NULL,
  `telefone` varchar(20) NOT NULL DEFAULT '',
  `email` varchar(255) NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_fornecedores`
--

INSERT INTO `tb_fornecedores` (`id_gestor`, `id_fornecedor`, `nome_fornecedor`, `produto_fornecido`, `telefone`, `email`) VALUES
(1, 4, 'Atacadão', 'Arroz', '', ''),
(1, 5, 'JP story', 'Feijão', '', ''),
(5, 7, 'Delly\'s Food Service', 'Distribuidora food service completa (hambúrguer, batata frita, queijos, carnes, molhos, arroz, feijão, descartáveis e bebidas)', '0800 212 4040', 'sac@dellys.com.br'),
(5, 8, 'Assaí Atacadista', 'Atacarejo geral para insumos de cozinha, bebidas, conservas, óleos, frios e embalagens.', '(14) 99779-8392', 'loja061.televendas@assai.com.br'),
(5, 9, 'Vinhais Alimentos', 'Distribuidora food service para hamburguerias e restaurantes com venda fracionada.', '(11) 3933-5454', 'site@vinhais.com.br'),
(5, 10, 'Atacadão (Atendimento Empresas)', 'Secos, arroz, feijão, macarrão, óleo, molhos, bebidas e itens de limpeza/descartáveis.', '(11) 4004-5626', 'faleconosco@atacadao.com.br'),
(5, 11, 'Grupo Food Service', 'Carnes porcionadas, aves, bovinos, congelados, estocáveis e embalagens para restaurantes.', '(11) 94072-2321', 'Food@gmail.com'),
(5, 12, 'McCain do Brasil (Food Service)', 'Batata pré-frita congelada para porções, acompanhamentos e lanches.', '(21) 97201-1011', 'sac@mccain.com.br'),
(5, 13, 'BRF (Sadia / Perdigão Food Service)', 'Hambúrguer, bacon, peito de frango, filé de frango empanado, frios e margarina.', '0800 702 8800', 'saccentral@brf.com'),
(5, 14, 'Coca-Cola FEMSA Brasil', 'Coca-Cola (lata, 600ml, 1L, 2L), Fanta Laranja e linhas de sucos/águas.', '(11) 98964-9773', 'cobranca@kof.com'),
(5, 15, 'Ambev (Plataforma BEES)', 'Guaraná Antarctica 2L, refrigerantes em geral, cervejas e águas.', '0800 725 0001', 'faleconosco@ambev.com.br'),
(5, 16, 'Suco Prat\'s (Pratsy Indústria de Bebidas)', 'Fabricante do suco de laranja integral Prat\'s (garrafas 330ml, 900ml e 1,7L).', '(44) 3141-5000', 'sac@prats.com.br'),
(5, 17, 'São João Alimentos', 'Indústria matriz em Santa Cruz do Rio Pardo. Fornece arroz branco, parboilizado, integral e feijão carioca em fardos.', '(14) 3332-3000', 'sac@saojoaoalimentos.com'),
(5, 18, 'Cerealista Rosalito', 'Beneficiadora local de arroz polido, feijão carioca, feijão preto e grãos empacotados.', '(14) 3332-5500', 'sac@rosalito.com.br'),
(5, 19, 'Santa Massa Alimentos', 'Fornece pão de alho tradicional, picante, bolinha, pasta de alho e farofa.', '(14) 3372-8111', 'santamassa@gmail.com'),
(5, 20, 'Supremo Pão de Alho', 'Fabricante local em Santa Cruz do Rio Pardo especializado em pães de alho para churrasco e porções.', '(14) 98140-4344', 'supremopaodealho@hotmail.com'),
(5, 21, 'Bauru Ovos Distribuidora', 'Distribuidora especializada em ovos de codorna (para sua porção), ovos brancos, vermelhos e caipiras a granel ou em caixas.', '(14) 99104-2727', 'ovos@gmail.com.br'),
(5, 22, 'Dom Rafa Hortifruti', '10.  	• alface, tomate, cebola roxa, cenoura, pepino e laranja para suco.', '(14) 99708-6688', 'Horti@gmail.com'),
(5, 23, 'Sacolão da Horta', 'Hortifrúti local em Santa Cruz do Rio Pardo para reposição diária de folhas e legumes frescos.', '(14) 3372-5357', 'sacolao123@gmail.com'),
(5, 24, 'PH Hortifruti Distribuidora', 'Distribuidora voltada para cozinhas industriais e restaurantes com entrega de verduras, legumes selecionados e higienizados.', '(14) 99120-7618', 'Phhhorti@gmail.com'),
(5, 25, 'Solito Alimentos', 'Fornece óleo de soja refinado em fardos, arroz, feijão e farináceos.', '(14) 3332-1866', 'alimentos@gmail.com'),
(5, 26, 'JR Distribuidora de Alimentos', 'Distribuidora regional de frios, laticínios, congelados e itens gerais para lanchonetes e restaurantes.', '(14) 99647-9766', 'JRdistribui@gmail.com');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_fornecedor_estoque`
--

CREATE TABLE `tb_fornecedor_estoque` (
  `id_fornecedor` int(11) NOT NULL,
  `id_ingrediente` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_fornecedor_estoque`
--

INSERT INTO `tb_fornecedor_estoque` (`id_fornecedor`, `id_ingrediente`) VALUES
(7, 22),
(7, 23),
(7, 24),
(7, 26),
(7, 27),
(7, 30),
(7, 31),
(8, 9),
(8, 10),
(8, 11),
(8, 12),
(8, 13),
(8, 14),
(8, 15),
(8, 16),
(8, 26),
(8, 27),
(8, 29),
(8, 30),
(8, 31),
(8, 32),
(8, 39),
(8, 42),
(9, 23),
(9, 24),
(10, 7),
(10, 9),
(10, 11),
(10, 12),
(10, 13),
(10, 14),
(10, 15),
(10, 16),
(10, 32),
(11, 17),
(11, 18),
(11, 19),
(11, 25),
(12, 33),
(13, 19),
(13, 20),
(13, 21),
(13, 22),
(13, 28),
(14, 45),
(14, 46),
(14, 47),
(14, 48),
(14, 49),
(15, 50),
(16, 51),
(17, 7),
(17, 8),
(18, 7),
(18, 8),
(19, 44),
(20, 44),
(21, 43),
(22, 34),
(22, 35),
(22, 36),
(22, 37),
(22, 38),
(22, 39),
(22, 40),
(22, 41),
(23, 34),
(23, 35),
(23, 36),
(23, 37),
(23, 38),
(23, 40),
(23, 41),
(24, 34),
(24, 35),
(24, 36),
(24, 37),
(24, 38),
(24, 39),
(24, 41),
(25, 8),
(25, 10),
(26, 17),
(26, 24),
(26, 25),
(26, 28),
(26, 29);

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_funcionarios`
--

CREATE TABLE `tb_funcionarios` (
  `id_funcionario` int(11) NOT NULL,
  `id_gestor` int(11) NOT NULL,
  `nome_completo` text NOT NULL,
  `CPF_funcionario` varchar(14) NOT NULL,
  `inicio_trabalho` date NOT NULL,
  `desempenho` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_funcionarios`
--

INSERT INTO `tb_funcionarios` (`id_funcionario`, `id_gestor`, `nome_completo`, `CPF_funcionario`, `inicio_trabalho`, `desempenho`) VALUES
(6, 1, 'Vagner Felipe Batista', '49247588748', '2026-06-14', 'Está em treinamento'),
(7, 1, 'Nicole Nizoli Benkard', '49247588748', '2000-04-15', 'Está em treinamento'),
(8, 5, 'Nicole Nizoli Benkard', '46915485805', '2026-08-31', 'Em treinamento'),
(9, 5, 'lucas soares', '12345678912', '2026-09-23', 'treinamento'),
(10, 5, 'Rafael Mendes Carvalho', '707.536.455-30', '2022-02-07', 'Excelente'),
(11, 5, 'Juliana Ferreira Lima', '681.518.844-28', '2022-05-16', 'Ótimo'),
(12, 5, 'Carlos Eduardo Nogueira', '729.405.576-91', '2022-09-05', 'Excelente'),
(13, 5, 'Patrícia Alves Moreira', '169.871.689-38', '2023-01-09', 'Excelente'),
(14, 5, 'Bruno Henrique Teixeira', '768.469.788-95', '2023-03-20', 'Ótimo'),
(15, 5, 'Camila Rodrigues Pinto', '093.208.196-70', '2023-06-12', 'Bom'),
(16, 5, 'Thiago Oliveira Ramos', '727.317.784-90', '2023-08-28', 'Bom'),
(17, 5, 'Fernanda Costa Barbosa', '864.887.663-09', '2023-11-06', 'Bom'),
(18, 5, 'Gustavo Lopes Andrade', '440.029.787-02', '2024-01-15', 'Bom'),
(19, 5, 'Larissa Martins Duarte', '534.778.768-65', '2024-03-04', 'Bom'),
(20, 5, 'Diego Santana Freitas', '045.717.847-75', '2024-05-20', 'Bom'),
(21, 5, 'Beatriz Cardoso Machado', '267.147.513-62', '2024-07-08', 'Ótimo'),
(22, 5, 'Marcelo Ribeiro Azevedo', '627.010.798-96', '2024-09-23', 'Ótimo'),
(23, 5, 'Aline Gomes Cavalcante', '193.319.084-15', '2025-01-13', 'Bom'),
(24, 5, 'Leandro Batista Rocha', '520.599.728-17', '2025-03-17', 'Bom'),
(25, 5, 'Vanessa Souza Monteiro', '379.298.416-42', '2025-06-02', 'Bom'),
(26, 5, 'Rodrigo Pereira Nunes', '229.486.628-26', '2025-08-18', 'Regular'),
(27, 5, 'Tatiane Araújo Correia', '360.161.055-89', '2026-01-12', 'Bom'),
(28, 5, 'Felipe Augusto Vieira', '008.754.751-16', '2026-05-04', 'Em treinamento'),
(29, 5, 'Mariana Castro Siqueira', '779.922.983-73', '2026-09-14', 'Em treinamento');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_gestor`
--

CREATE TABLE `tb_gestor` (
  `CNPJ` varchar(18) NOT NULL,
  `CPF` varchar(14) NOT NULL,
  `id_gestor` int(11) NOT NULL,
  `nome_gestor` text NOT NULL,
  `e_mail` varchar(255) NOT NULL,
  `senha` varchar(255) NOT NULL,
  `restaurante` varchar(100) NOT NULL,
  `telefone` varchar(20) NOT NULL,
  `status` enum('pendente','ativo') DEFAULT 'pendente',
  `data_vencimento` date DEFAULT NULL,
  `data_liberacao` date DEFAULT NULL,
  `token_cardapio` varchar(32) DEFAULT NULL,
  `assinatura_cancelada` tinyint(1) NOT NULL DEFAULT 0,
  `data_cancelamento` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_gestor`
--

INSERT INTO `tb_gestor` (`CNPJ`, `CPF`, `id_gestor`, `nome_gestor`, `e_mail`, `senha`, `restaurante`, `telefone`, `status`, `data_vencimento`, `data_liberacao`, `token_cardapio`, `assinatura_cancelada`, `data_cancelamento`) VALUES
('11111111', '46915485805', 5, 'Nicole Nizoli Benkard', 'nicole.benkard@aluno.senai.br', '$2y$10$mY3ajuJk4sowI7eNdxN5AO2eI188gZJLjPHNWwIEKEejGVwYtp/Z.', 'Nicole Nizoli Benkard', '11111111111', 'ativo', '2028-01-19', NULL, '3b4f24a1d552d035cb23cd93', 0, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_item_ficha_tecnica`
--

CREATE TABLE `tb_item_ficha_tecnica` (
  `id_item_ficha` int(11) NOT NULL,
  `id_prato` int(11) NOT NULL,
  `id_ingrediente` int(11) NOT NULL,
  `quantidade_necessaria` decimal(10,3) NOT NULL,
  `unidade` varchar(10) NOT NULL DEFAULT 'g',
  `modo_preparo` varchar(600) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_item_ficha_tecnica`
--

INSERT INTO `tb_item_ficha_tecnica` (`id_item_ficha`, `id_prato`, `id_ingrediente`, `quantidade_necessaria`, `unidade`, `modo_preparo`) VALUES
(5, 3, 7, 150.000, 'g', NULL),
(6, 4, 7, 150.000, 'g', NULL),
(7, 16, 7, 150.000, 'g', NULL),
(8, 23, 7, 150.000, 'g', NULL),
(9, 16, 8, 100.000, 'g', NULL),
(10, 24, 9, 120.000, 'g', NULL),
(11, 3, 10, 10.000, 'ml', NULL),
(12, 4, 10, 15.000, 'ml', NULL),
(13, 8, 10, 100.000, 'ml', NULL),
(14, 14, 10, 5.000, 'ml', NULL),
(15, 15, 10, 5.000, 'ml', NULL),
(16, 16, 10, 80.000, 'ml', NULL),
(17, 23, 10, 80.000, 'ml', NULL),
(18, 4, 11, 5.000, 'ml', NULL),
(19, 9, 11, 5.000, 'ml', NULL),
(20, 10, 11, 5.000, 'ml', NULL),
(21, 16, 11, 5.000, 'ml', NULL),
(22, 23, 11, 5.000, 'ml', NULL),
(23, 24, 11, 15.000, 'ml', NULL),
(24, 4, 12, 5.000, 'ml', NULL),
(25, 7, 12, 10.000, 'ml', NULL),
(26, 3, 13, 3.000, 'g', NULL),
(27, 4, 13, 4.000, 'g', NULL),
(28, 6, 13, 2.000, 'g', NULL),
(29, 8, 13, 3.000, 'g', NULL),
(30, 9, 13, 2.000, 'g', NULL),
(31, 10, 13, 3.000, 'g', NULL),
(32, 14, 13, 1.000, 'g', NULL),
(33, 15, 13, 1.000, 'g', NULL),
(34, 16, 13, 4.000, 'g', NULL),
(35, 23, 13, 2.000, 'g', NULL),
(36, 24, 13, 3.000, 'g', NULL),
(37, 7, 14, 2.000, 'g', NULL),
(38, 3, 15, 1.000, 'g', NULL),
(39, 9, 15, 1.000, 'g', NULL),
(40, 10, 15, 1.000, 'g', NULL),
(41, 14, 15, 0.500, 'g', NULL),
(42, 15, 15, 0.500, 'g', NULL),
(43, 24, 15, 1.000, 'g', NULL),
(44, 9, 16, 1.000, 'g', NULL),
(45, 3, 17, 150.000, 'g', NULL),
(46, 4, 17, 200.000, 'g', NULL),
(47, 10, 18, 120.000, 'g', NULL),
(48, 9, 19, 120.000, 'g', NULL),
(49, 16, 20, 150.000, 'g', NULL),
(50, 23, 20, 180.000, 'g', NULL),
(51, 9, 21, 40.000, 'g', NULL),
(52, 15, 21, 40.000, 'g', NULL),
(53, 14, 22, 1.000, 'un', NULL),
(54, 15, 22, 1.000, 'un', NULL),
(55, 14, 23, 1.000, 'un', NULL),
(56, 15, 23, 1.000, 'un', NULL),
(57, 14, 24, 2.000, 'un', NULL),
(58, 15, 24, 2.000, 'un', NULL),
(59, 23, 25, 3.000, 'un', NULL),
(60, 14, 26, 15.000, 'g', NULL),
(61, 14, 27, 15.000, 'g', NULL),
(62, 14, 28, 10.000, 'g', NULL),
(63, 15, 28, 10.000, 'g', NULL),
(64, 3, 29, 50.000, 'ml', NULL),
(65, 3, 30, 10.000, 'ml', NULL),
(66, 3, 31, 20.000, 'ml', NULL),
(67, 23, 32, 60.000, 'g', NULL),
(68, 24, 32, 30.000, 'g', NULL),
(69, 4, 33, 150.000, 'g', NULL),
(70, 8, 33, 300.000, 'g', NULL),
(71, 4, 34, 0.150, 'un', NULL),
(72, 7, 34, 0.250, 'un', NULL),
(73, 9, 34, 0.100, 'un', NULL),
(74, 15, 34, 0.100, 'un', NULL),
(75, 16, 34, 0.150, 'un', NULL),
(76, 4, 35, 60.000, 'g', NULL),
(77, 7, 35, 80.000, 'g', NULL),
(78, 15, 35, 30.000, 'g', NULL),
(79, 16, 35, 40.000, 'g', NULL),
(80, 24, 35, 100.000, 'g', NULL),
(81, 3, 36, 30.000, 'g', NULL),
(82, 4, 36, 80.000, 'g', NULL),
(83, 7, 36, 30.000, 'g', NULL),
(84, 10, 36, 20.000, 'g', NULL),
(85, 16, 36, 20.000, 'g', NULL),
(86, 23, 36, 15.000, 'g', NULL),
(87, 24, 36, 40.000, 'g', NULL),
(88, 7, 37, 60.000, 'g', NULL),
(89, 7, 38, 40.000, 'g', NULL),
(90, 3, 39, 5.000, 'g', NULL),
(91, 4, 39, 5.000, 'g', NULL),
(92, 9, 39, 3.000, 'g', NULL),
(93, 10, 39, 3.000, 'g', NULL),
(94, 16, 39, 6.000, 'g', NULL),
(95, 23, 39, 4.000, 'g', NULL),
(96, 3, 40, 0.100, 'un', NULL),
(97, 6, 40, 0.100, 'un', NULL),
(98, 13, 41, 4.000, 'un', NULL),
(99, 13, 42, 100.000, 'g', NULL),
(100, 6, 43, 15.000, 'un', NULL),
(101, 11, 44, 4.000, 'un', NULL),
(102, 19, 45, 1.000, 'un', NULL),
(103, 20, 46, 1.000, 'un', NULL),
(104, 21, 47, 1.000, 'un', NULL),
(105, 22, 48, 1.000, 'un', NULL),
(106, 18, 49, 1.000, 'un', NULL),
(107, 17, 50, 1.000, 'un', NULL),
(108, 12, 51, 1.000, 'un', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_itens_pedido`
--

CREATE TABLE `tb_itens_pedido` (
  `id_itens` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `id_prato` int(11) NOT NULL,
  `nome_prato` varchar(255) DEFAULT NULL,
  `preco_unitario` decimal(10,2) DEFAULT NULL,
  `quantidade_vendida` int(11) NOT NULL,
  `data_hora` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_itens_pedido`
--

INSERT INTO `tb_itens_pedido` (`id_itens`, `id_pedido`, `id_prato`, `nome_prato`, `preco_unitario`, `quantidade_vendida`, `data_hora`) VALUES
(1, 1, 3, 'strogonoff', 35.10, 1, '2026-09-23 21:24:05'),
(2, 2, 23, 'Parmegiana de Frango', 26.00, 1, '2026-10-12 14:46:00'),
(3, 2, 22, 'Coca Cola - Lata', 5.00, 1, '2026-10-12 15:18:00'),
(4, 2, 14, 'Amburgue - Cheedar', 30.15, 1, '2026-10-12 15:18:00'),
(5, 3, 9, 'Espetinho de Medalhão', 12.00, 1, '2026-10-13 21:05:00'),
(6, 3, 12, 'Suco de laranja', 13.50, 1, '2026-10-13 21:05:00'),
(7, 4, 23, 'Parmegiana de Frango', 26.00, 1, '2026-10-14 16:06:00'),
(8, 4, 17, 'Guaraná Antartica - 2L', 8.99, 1, '2026-10-14 16:32:00'),
(9, 4, 24, 'Macarronada', 19.90, 1, '2026-10-14 16:32:00'),
(10, 5, 4, 'Bifé acebolado', 39.90, 2, '2026-10-16 00:37:00'),
(11, 5, 12, 'Suco de laranja', 13.50, 2, '2026-10-16 00:37:00'),
(12, 5, 23, 'Parmegiana de Frango', 26.00, 3, '2026-10-16 01:11:00'),
(13, 5, 13, 'Suco de Laranja - Copo', 14.20, 1, '2026-10-16 01:11:00'),
(14, 5, 22, 'Coca Cola - Lata', 5.00, 2, '2026-10-16 01:11:00'),
(15, 5, 11, 'Pão de Alho', 10.60, 1, '2026-10-16 01:11:00'),
(16, 6, 23, 'Parmegiana de Frango', 26.00, 1, '2026-10-16 23:13:00'),
(17, 6, 9, 'Espetinho de Medalhão', 12.00, 1, '2026-10-16 23:13:00'),
(18, 6, 17, 'Guaraná Antartica - 2L', 8.99, 1, '2026-10-16 23:13:00'),
(19, 6, 24, 'Macarronada', 19.90, 1, '2026-10-16 23:13:00'),
(20, 6, 18, 'Fanta Laranja - Lata', 5.00, 1, '2026-10-16 23:13:00'),
(21, 6, 14, 'Amburgue - Cheedar', 30.15, 1, '2026-10-16 23:13:00'),
(22, 6, 11, 'Pão de Alho', 10.60, 1, '2026-10-16 23:13:00'),
(23, 7, 16, 'Refeição - Filé de Frango Empanado', 22.99, 1, '2026-10-17 14:52:00'),
(24, 8, 14, 'Amburgue - Cheedar', 30.15, 1, '2026-10-18 00:07:00'),
(25, 8, 16, 'Refeição - Filé de Frango Empanado', 22.99, 1, '2026-10-18 00:28:00'),
(26, 8, 22, 'Coca Cola - Lata', 5.00, 1, '2026-10-18 00:28:00'),
(27, 8, 8, 'Batata Frita', 26.70, 1, '2026-10-18 00:28:00'),
(28, 9, 16, 'Refeição - Filé de Frango Empanado', 22.99, 1, '2026-10-18 00:12:00'),
(29, 9, 22, 'Coca Cola - Lata', 5.00, 1, '2026-10-18 00:12:00'),
(30, 9, 4, 'Bifé acebolado', 39.90, 2, '2026-10-18 00:12:00'),
(31, 9, 18, 'Fanta Laranja - Lata', 5.00, 2, '2026-10-18 00:12:00'),
(32, 9, 8, 'Batata Frita', 26.70, 1, '2026-10-18 00:12:00'),
(33, 10, 3, 'strogonoff', 35.10, 1, '2026-10-18 15:01:00'),
(34, 10, 18, 'Fanta Laranja - Lata', 5.00, 1, '2026-10-18 15:01:00'),
(35, 10, 14, 'Amburgue - Cheedar', 30.15, 1, '2026-10-18 15:01:00'),
(36, 10, 23, 'Parmegiana de Frango', 26.00, 1, '2026-10-18 15:01:00'),
(37, 10, 13, 'Suco de Laranja - Copo', 14.20, 1, '2026-10-18 15:01:00'),
(38, 11, 24, 'Macarronada', 19.90, 1, '2026-10-18 16:04:00'),
(39, 11, 21, 'Coca Cola - 600ml', 6.00, 1, '2026-10-18 16:04:00'),
(40, 11, 23, 'Parmegiana de Frango', 26.00, 1, '2026-10-18 16:04:00'),
(41, 11, 17, 'Guaraná Antartica - 2L', 8.99, 1, '2026-10-18 16:04:00'),
(42, 11, 7, 'Salada', 18.50, 1, '2026-10-18 16:04:00'),
(43, 12, 23, 'Parmegiana de Frango', 26.00, 2, '2026-10-19 00:53:00'),
(44, 12, 18, 'Fanta Laranja - Lata', 5.00, 1, '2026-10-19 00:53:00'),
(45, 12, 22, 'Coca Cola - Lata', 5.00, 1, '2026-10-19 01:14:00'),
(46, 12, 3, 'strogonoff', 35.10, 1, '2026-10-19 01:14:00'),
(47, 12, 21, 'Coca Cola - 600ml', 6.00, 1, '2026-10-19 01:14:00'),
(48, 12, 11, 'Pão de Alho', 10.60, 1, '2026-10-19 01:14:00'),
(49, 13, 16, 'Refeição - Filé de Frango Empanado', 22.99, 2, '2026-10-19 22:36:00'),
(50, 13, 12, 'Suco de laranja', 13.50, 1, '2026-10-19 22:36:00'),
(51, 13, 20, 'Coca Cola - 1L', 8.00, 1, '2026-10-19 23:04:00'),
(52, 13, 24, 'Macarronada', 19.90, 1, '2026-10-19 23:04:00'),
(53, 13, 17, 'Guaraná Antartica - 2L', 8.99, 1, '2026-10-19 23:04:00'),
(54, 14, 4, 'Bifé acebolado', 39.90, 1, '2026-10-22 00:37:00'),
(55, 14, 22, 'Coca Cola - Lata', 5.00, 1, '2026-10-22 00:37:00'),
(56, 14, 15, 'Amburgue - Bacon', 22.99, 1, '2026-10-22 00:37:00'),
(57, 14, 17, 'Guaraná Antartica - 2L', 8.99, 1, '2026-10-22 00:37:00'),
(58, 14, 7, 'Salada', 18.50, 1, '2026-10-22 00:37:00'),
(59, 15, 14, 'Amburgue - Cheedar', 30.15, 1, '2026-10-21 14:59:00'),
(60, 15, 22, 'Coca Cola - Lata', 5.00, 1, '2026-10-21 14:59:00'),
(61, 15, 9, 'Espetinho de Medalhão', 12.00, 1, '2026-10-21 14:59:00'),
(62, 15, 19, 'Coca Cola - 2L', 13.99, 1, '2026-10-21 15:30:00'),
(63, 16, 15, 'Amburgue - Bacon', 22.99, 1, '2026-10-21 21:22:00'),
(64, 16, 3, 'strogonoff', 35.10, 1, '2026-10-21 21:22:00'),
(65, 16, 18, 'Fanta Laranja - Lata', 5.00, 1, '2026-10-21 21:22:00'),
(66, 16, 8, 'Batata Frita', 26.70, 1, '2026-10-21 21:22:00'),
(67, 17, 14, 'Amburgue - Cheedar', 30.15, 1, '2026-10-22 21:46:00'),
(68, 17, 13, 'Suco de Laranja - Copo', 14.20, 1, '2026-10-22 21:46:00'),
(69, 17, 16, 'Refeição - Filé de Frango Empanado', 22.99, 1, '2026-10-22 21:46:00'),
(70, 17, 19, 'Coca Cola - 2L', 13.99, 1, '2026-10-22 21:46:00'),
(71, 17, 11, 'Pão de Alho', 10.60, 1, '2026-10-22 21:46:00'),
(72, 18, 23, 'Parmegiana de Frango', 26.00, 1, '2026-10-22 16:19:00'),
(73, 18, 22, 'Coca Cola - Lata', 5.00, 1, '2026-10-22 16:19:00'),
(74, 18, 4, 'Bifé acebolado', 39.90, 1, '2026-10-22 16:19:00'),
(75, 18, 21, 'Coca Cola - 600ml', 6.00, 1, '2026-10-22 16:19:00'),
(76, 18, 3, 'strogonoff', 35.10, 1, '2026-10-22 16:37:00'),
(77, 18, 11, 'Pão de Alho', 10.60, 1, '2026-10-22 16:37:00'),
(78, 19, 16, 'Refeição - Filé de Frango Empanado', 22.99, 1, '2026-10-23 22:07:00'),
(79, 19, 19, 'Coca Cola - 2L', 13.99, 1, '2026-10-23 22:07:00'),
(80, 19, 14, 'Amburgue - Cheedar', 30.15, 1, '2026-10-23 22:07:00'),
(81, 20, 9, 'Espetinho de Medalhão', 12.00, 1, '2026-10-23 22:34:00'),
(82, 20, 17, 'Guaraná Antartica - 2L', 8.99, 1, '2026-10-23 22:34:00'),
(83, 20, 16, 'Refeição - Filé de Frango Empanado', 22.99, 1, '2026-10-23 22:34:00'),
(84, 20, 19, 'Coca Cola - 2L', 13.99, 1, '2026-10-23 23:10:00'),
(85, 21, 15, 'Amburgue - Bacon', 22.99, 1, '2026-10-24 15:19:00'),
(86, 21, 12, 'Suco de laranja', 13.50, 2, '2026-10-24 15:19:00'),
(87, 21, 4, 'Bifé acebolado', 39.90, 1, '2026-10-24 15:19:00'),
(88, 21, 6, 'Porção Ovo de Codorno', 15.00, 1, '2026-10-24 15:19:00'),
(89, 22, 14, 'Amburgue - Cheedar', 30.15, 1, '2026-10-24 21:04:00'),
(90, 22, 22, 'Coca Cola - Lata', 5.00, 1, '2026-10-24 21:04:00'),
(91, 22, 23, 'Parmegiana de Frango', 26.00, 1, '2026-10-24 21:04:00'),
(92, 22, 19, 'Coca Cola - 2L', 13.99, 1, '2026-10-24 21:04:00'),
(93, 22, 4, 'Bifé acebolado', 39.90, 1, '2026-10-24 21:04:00'),
(94, 22, 18, 'Fanta Laranja - Lata', 5.00, 1, '2026-10-24 21:04:00'),
(95, 22, 16, 'Refeição - Filé de Frango Empanado', 22.99, 1, '2026-10-24 21:04:00'),
(96, 22, 21, 'Coca Cola - 600ml', 6.00, 1, '2026-10-24 21:04:00'),
(97, 22, 7, 'Salada', 18.50, 1, '2026-10-24 21:04:00'),
(98, 23, 23, 'Parmegiana de Frango', 26.00, 1, '2026-10-25 21:31:00'),
(99, 24, 4, 'Bifé acebolado', 39.90, 2, '2026-10-27 16:51:00'),
(100, 24, 21, 'Coca Cola - 600ml', 6.00, 1, '2026-10-27 16:51:00'),
(101, 24, 12, 'Suco de laranja', 13.50, 1, '2026-10-27 16:51:00'),
(102, 24, 6, 'Porção Ovo de Codorno', 15.00, 1, '2026-10-27 16:51:00'),
(103, 25, 9, 'Espetinho de Medalhão', 12.00, 1, '2026-10-27 14:51:00'),
(104, 25, 3, 'strogonoff', 35.10, 1, '2026-10-27 14:51:00'),
(105, 25, 6, 'Porção Ovo de Codorno', 15.00, 1, '2026-10-27 15:17:00'),
(106, 26, 16, 'Refeição - Filé de Frango Empanado', 22.99, 1, '2026-10-31 23:10:00'),
(107, 26, 12, 'Suco de laranja', 13.50, 1, '2026-10-31 23:10:00'),
(108, 27, 14, 'Amburgue - Cheedar', 30.15, 1, '2026-10-31 15:17:00'),
(109, 27, 19, 'Coca Cola - 2L', 13.99, 1, '2026-10-31 15:17:00'),
(110, 28, 15, 'Amburgue - Bacon', 22.99, 2, '2026-10-31 22:44:00'),
(111, 28, 19, 'Coca Cola - 2L', 13.99, 1, '2026-10-31 22:44:00'),
(112, 28, 17, 'Guaraná Antartica - 2L', 8.99, 1, '2026-10-31 22:44:00'),
(113, 29, 14, 'Amburgue - Cheedar', 30.15, 1, '2026-11-01 23:30:00'),
(114, 30, 9, 'Espetinho de Medalhão', 12.00, 1, '2026-11-01 21:34:00'),
(115, 30, 22, 'Coca Cola - Lata', 5.00, 3, '2026-11-01 21:34:00'),
(116, 30, 23, 'Parmegiana de Frango', 26.00, 1, '2026-11-01 21:34:00'),
(117, 30, 12, 'Suco de laranja', 13.50, 1, '2026-11-01 21:34:00'),
(118, 30, 24, 'Macarronada', 19.90, 1, '2026-11-01 21:34:00'),
(119, 30, 16, 'Refeição - Filé de Frango Empanado', 22.99, 1, '2026-11-01 21:34:00'),
(120, 30, 7, 'Salada', 18.50, 1, '2026-11-01 21:34:00'),
(121, 31, 24, 'Macarronada', 19.90, 1, '2026-11-02 15:36:00'),
(122, 31, 4, 'Bifé acebolado', 39.90, 1, '2026-11-02 15:36:00'),
(123, 31, 23, 'Parmegiana de Frango', 26.00, 1, '2026-11-02 15:57:00'),
(124, 31, 22, 'Coca Cola - Lata', 5.00, 1, '2026-11-02 15:57:00'),
(125, 31, 11, 'Pão de Alho', 10.60, 1, '2026-11-02 15:57:00'),
(126, 32, 3, 'strogonoff', 35.10, 1, '2026-11-03 16:46:00'),
(127, 32, 17, 'Guaraná Antartica - 2L', 8.99, 1, '2026-11-03 16:46:00'),
(128, 32, 24, 'Macarronada', 19.90, 1, '2026-11-03 17:14:00'),
(129, 32, 8, 'Batata Frita', 26.70, 1, '2026-11-03 17:14:00'),
(130, 33, 3, 'strogonoff', 35.10, 1, '2026-11-03 14:53:00'),
(131, 33, 18, 'Fanta Laranja - Lata', 5.00, 1, '2026-11-03 14:53:00'),
(132, 33, 4, 'Bifé acebolado', 39.90, 1, '2026-11-03 14:53:00'),
(133, 33, 22, 'Coca Cola - Lata', 5.00, 1, '2026-11-03 14:53:00'),
(134, 34, 9, 'Espetinho de Medalhão', 12.00, 2, '2026-11-04 14:52:00'),
(135, 34, 20, 'Coca Cola - 1L', 8.00, 1, '2026-11-04 15:30:00'),
(136, 34, 13, 'Suco de Laranja - Copo', 14.20, 1, '2026-11-04 15:30:00'),
(137, 34, 8, 'Batata Frita', 26.70, 1, '2026-11-04 15:30:00'),
(138, 35, 16, 'Refeição - Filé de Frango Empanado', 22.99, 1, '2026-11-05 14:59:00'),
(139, 35, 18, 'Fanta Laranja - Lata', 5.00, 1, '2026-11-05 14:59:00'),
(140, 36, 23, 'Parmegiana de Frango', 26.00, 1, '2026-11-05 15:15:00'),
(141, 36, 17, 'Guaraná Antartica - 2L', 8.99, 1, '2026-11-05 15:15:00'),
(142, 37, 23, 'Parmegiana de Frango', 26.00, 1, '2026-11-05 22:27:00'),
(143, 37, 17, 'Guaraná Antartica - 2L', 8.99, 1, '2026-11-05 22:27:00'),
(144, 38, 4, 'Bifé acebolado', 39.90, 1, '2026-11-06 23:44:00'),
(145, 38, 21, 'Coca Cola - 600ml', 6.00, 1, '2026-11-07 00:04:00'),
(146, 38, 3, 'strogonoff', 35.10, 1, '2026-11-07 00:04:00'),
(147, 38, 18, 'Fanta Laranja - Lata', 5.00, 1, '2026-11-07 00:04:00'),
(148, 38, 8, 'Batata Frita', 26.70, 1, '2026-11-07 00:04:00'),
(149, 39, 14, 'Amburgue - Cheedar', 30.15, 1, '2026-11-08 00:50:00'),
(150, 39, 17, 'Guaraná Antartica - 2L', 8.99, 1, '2026-11-08 00:50:00'),
(151, 39, 16, 'Refeição - Filé de Frango Empanado', 22.99, 1, '2026-11-08 00:50:00'),
(152, 39, 19, 'Coca Cola - 2L', 13.99, 1, '2026-11-08 00:50:00'),
(153, 39, 11, 'Pão de Alho', 10.60, 1, '2026-11-08 00:50:00'),
(154, 40, 14, 'Amburgue - Cheedar', 30.15, 1, '2026-11-07 16:38:00'),
(155, 40, 3, 'strogonoff', 35.10, 1, '2026-11-07 16:38:00'),
(156, 41, 3, 'strogonoff', 35.10, 2, '2026-11-08 14:47:00'),
(157, 41, 20, 'Coca Cola - 1L', 8.00, 1, '2026-11-08 14:47:00'),
(158, 41, 13, 'Suco de Laranja - Copo', 14.20, 1, '2026-11-08 15:12:00'),
(159, 41, 23, 'Parmegiana de Frango', 26.00, 1, '2026-11-08 15:12:00'),
(160, 41, 19, 'Coca Cola - 2L', 13.99, 1, '2026-11-08 15:12:00'),
(161, 41, 8, 'Batata Frita', 26.70, 1, '2026-11-08 15:12:00'),
(162, 42, 24, 'Macarronada', 19.90, 1, '2026-11-08 22:14:00'),
(163, 42, 17, 'Guaraná Antartica - 2L', 8.99, 1, '2026-11-08 22:14:00'),
(164, 43, 4, 'Bifé acebolado', 39.90, 1, '2026-11-08 14:51:00'),
(165, 43, 19, 'Coca Cola - 2L', 13.99, 1, '2026-11-08 14:51:00'),
(166, 43, 16, 'Refeição - Filé de Frango Empanado', 22.99, 1, '2026-11-08 14:51:00'),
(167, 43, 21, 'Coca Cola - 600ml', 6.00, 1, '2026-11-08 14:51:00'),
(168, 43, 24, 'Macarronada', 19.90, 1, '2026-11-08 14:51:00'),
(169, 43, 15, 'Amburgue - Bacon', 22.99, 1, '2026-11-08 14:51:00'),
(170, 43, 22, 'Coca Cola - Lata', 5.00, 1, '2026-11-08 14:51:00'),
(171, 43, 8, 'Batata Frita', 26.70, 1, '2026-11-08 14:51:00'),
(172, 44, 15, 'Amburgue - Bacon', 22.99, 2, '2026-11-10 15:59:00'),
(173, 44, 21, 'Coca Cola - 600ml', 6.00, 1, '2026-11-10 15:59:00'),
(174, 44, 8, 'Batata Frita', 26.70, 1, '2026-11-10 16:16:00'),
(175, 45, 4, 'Bifé acebolado', 39.90, 1, '2026-11-12 00:36:00'),
(176, 45, 19, 'Coca Cola - 2L', 13.99, 1, '2026-11-12 00:36:00'),
(177, 46, 9, 'Espetinho de Medalhão', 12.00, 1, '2026-11-13 23:21:00'),
(178, 46, 21, 'Coca Cola - 600ml', 6.00, 1, '2026-11-13 23:21:00'),
(179, 46, 15, 'Amburgue - Bacon', 22.99, 1, '2026-11-13 23:21:00'),
(180, 46, 22, 'Coca Cola - Lata', 5.00, 1, '2026-11-13 23:21:00'),
(181, 46, 11, 'Pão de Alho', 10.60, 1, '2026-11-13 23:21:00'),
(182, 47, 14, 'Amburgue - Cheedar', 30.15, 1, '2026-11-13 14:50:00'),
(183, 47, 19, 'Coca Cola - 2L', 13.99, 1, '2026-11-13 14:50:00'),
(184, 48, 14, 'Amburgue - Cheedar', 30.15, 1, '2026-11-14 01:25:36'),
(185, 48, 22, 'Coca Cola - Lata', 5.00, 1, '2026-11-14 01:25:36'),
(186, 48, 8, 'Batata Frita', 26.70, 1, '2026-11-14 01:47:36'),
(187, 48, 19, 'Coca Cola - 2L', 13.99, 1, '2026-11-14 01:47:36'),
(188, 49, 16, 'Refeição - Filé de Frango Empanado', 22.99, 2, '2026-11-14 01:11:36'),
(189, 49, 22, 'Coca Cola - Lata', 5.00, 2, '2026-11-14 01:11:36'),
(190, 50, 23, 'Parmegiana de Frango', 26.00, 1, '2026-11-14 01:42:36'),
(191, 50, 15, 'Amburgue - Bacon', 22.99, 1, '2026-11-14 01:42:36'),
(192, 50, 17, 'Guaraná Antartica - 2L', 8.99, 1, '2026-11-14 01:42:36'),
(193, 51, 24, 'Macarronada', 19.90, 1, '2026-11-14 01:52:36'),
(194, 51, 12, 'Suco de laranja', 13.50, 1, '2026-11-14 01:52:36');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_kanban_tarefas`
--

CREATE TABLE `tb_kanban_tarefas` (
  `id_tarefa` int(11) NOT NULL,
  `id_gestor` int(11) NOT NULL,
  `titulo` varchar(150) NOT NULL,
  `descricao` text DEFAULT NULL,
  `responsavel` varchar(150) DEFAULT NULL,
  `tags` varchar(255) DEFAULT NULL,
  `status` enum('ideias','a_fazer','em_progresso','em_revisao','concluida') NOT NULL DEFAULT 'ideias',
  `prioridade` enum('baixa','media','alta','urgente') NOT NULL DEFAULT 'media',
  `data_limite` date DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp(),
  `data_atualizacao` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_kanban_tarefas`
--

INSERT INTO `tb_kanban_tarefas` (`id_tarefa`, `id_gestor`, `titulo`, `descricao`, `responsavel`, `tags`, `status`, `prioridade`, `data_limite`, `data_criacao`, `data_atualizacao`) VALUES
(13, 5, 'Repor estoque de bebidas antes do fim de semana', 'Conferir Coca-Cola, Guaraná e Fanta e fazer o pedido para a FEMSA e a Ambev.', 'Rafael Mendes Carvalho', 'Estoque, Compras', 'a_fazer', 'alta', '2026-11-18', '2026-11-13 11:07:40', '2026-11-14 11:07:40'),
(14, 5, 'Revisar a ficha técnica da Parmegiana de Frango', 'Ajustar as quantidades de queijo e molho e conferir o custo por porção.', 'Juliana Ferreira Lima', 'Ficha técnica, Custos', 'em_progresso', 'media', '2026-11-21', '2026-11-10 11:07:40', '2026-11-11 11:07:40'),
(15, 5, 'Treinar os garçons novos no sistema de comandas', 'Mostrar como abrir comanda, lançar rodadas e dar baixa na mesa.', 'Carlos Eduardo Nogueira', 'Equipe, Treinamento', 'em_progresso', 'alta', '2026-11-19', '2026-11-08 11:07:40', '2026-11-09 11:07:40'),
(16, 5, 'Renovar o alvará de funcionamento', 'Reunir os documentos e agendar o protocolo na prefeitura.', 'Patrícia Alves Moreira', 'Documentos, Legal', 'a_fazer', 'urgente', '2026-11-23', '2026-11-04 11:07:40', '2026-11-05 11:07:40'),
(17, 5, 'Trocar o óleo da fritadeira e registrar o descarte', 'Fazer a troca no fim do expediente e lançar o óleo usado como desperdício.', 'Bruno Henrique Teixeira', 'Cozinha, Desperdício', 'concluida', 'media', '2026-11-14', '2026-11-06 11:07:40', '2026-11-07 11:07:40'),
(18, 5, 'Negociar preço da carne com o Grupo Food Service', 'Pedir cotação de coxão mole e filé mignon e comparar com a JR Distribuidora.', 'Camila Rodrigues Pinto', 'Compras, Fornecedores', 'em_revisao', 'alta', '2026-11-20', '2026-11-07 11:07:40', '2026-11-08 11:07:40'),
(19, 5, 'Criar promoção de combo hambúrguer + refrigerante', 'Definir preço do combo e divulgar no cardápio digital e nas redes.', 'Thiago Oliveira Ramos', 'Marketing, Cardápio', 'ideias', 'media', NULL, '2026-11-11 11:07:40', '2026-11-12 11:07:40'),
(20, 5, 'Imprimir o QR Code das mesas 13 a 32', 'Gerar os QR Codes novos e plastificar para colar nas mesas.', 'Fernanda Costa Barbosa', 'Mesas, Cardápio digital', 'a_fazer', 'media', '2026-11-20', '2026-11-14 11:07:40', '2026-11-15 11:07:40'),
(21, 5, 'Fazer inventário geral do estoque', 'Contar tudo, comparar com o sistema e corrigir as diferenças.', 'Gustavo Lopes Andrade', 'Estoque, Inventário', 'em_progresso', 'alta', '2026-11-22', '2026-11-09 11:07:40', '2026-11-10 11:07:40'),
(22, 5, 'Descartar frios vencidos e registrar o desperdício', 'Separar queijo, bacon e suco vencidos e lançar na tela de Cálculos.', 'Larissa Martins Duarte', 'Desperdício, Higiene', 'concluida', 'alta', '2026-11-11', '2026-11-02 11:07:40', '2026-11-03 11:07:40'),
(23, 5, 'Estudar cardápio vegetariano', 'Levantar pratos sem carne e testar custo e aceitação com os clientes.', 'Diego Santana Freitas', 'Cardápio, Pesquisa', 'ideias', 'baixa', NULL, '2026-11-12 11:07:40', '2026-11-13 11:07:40'),
(24, 5, 'Revisar a escala de funcionários do próximo mês', 'Equilibrar folgas e reforçar sexta, sábado e domingo.', 'Beatriz Cardoso Machado', 'Equipe, Escala', 'em_revisao', 'media', '2026-11-19', '2026-11-11 11:07:40', '2026-11-12 11:07:40'),
(25, 5, 'Dedetização trimestral', 'Contratar empresa e combinar o dia, de preferência numa segunda-feira.', 'Marcelo Ribeiro Azevedo', 'Higiene, Manutenção', 'a_fazer', 'alta', '2026-11-26', '2026-11-10 11:07:40', '2026-11-11 11:07:40'),
(26, 5, 'Consertar a câmara fria', 'Chamar a assistência: está demorando para atingir a temperatura.', 'Aline Gomes Cavalcante', 'Manutenção, Urgente', 'em_progresso', 'urgente', '2026-11-17', '2026-11-14 11:07:40', '2026-11-15 11:07:40'),
(27, 5, 'Atualizar fotos dos pratos no cardápio digital', 'Tirar fotos novas dos hambúrgueres e da parmegiana com boa luz.', 'Leandro Batista Rocha', 'Cardápio, Marketing', 'a_fazer', 'baixa', '2026-11-30', '2026-11-12 11:07:40', '2026-11-13 11:07:40'),
(28, 5, 'Montar a planta da cozinha nova', 'Desenhar na tela de Layout a disposição de fogão, pias e bancadas.', 'Vanessa Souza Monteiro', 'Layout, Projeto', 'em_revisao', 'media', '2026-11-24', '2026-11-05 11:07:40', '2026-11-06 11:07:40'),
(29, 5, 'Pesquisa de satisfação com clientes', 'Criar um formulário curto e deixar o QR Code nas mesas.', 'Rodrigo Pereira Nunes', 'Clientes, Pesquisa', 'ideias', 'baixa', NULL, '2026-11-13 11:07:40', '2026-11-14 11:07:40'),
(30, 5, 'Pagar a mensalidade do sistema', 'Conferir o vencimento no Perfil e pagar a renovação.', 'Tatiane Araújo Correia', 'Financeiro, Assinatura', 'concluida', 'media', '2026-11-15', '2026-11-07 11:07:40', '2026-11-08 11:07:40'),
(31, 5, 'Comprar uniformes e toucas', 'Pedir para toda a equipe da cozinha e do salão.', 'Felipe Augusto Vieira', 'Equipe, Compras', 'a_fazer', 'media', '2026-11-25', '2026-11-11 11:07:40', '2026-11-12 11:07:40'),
(32, 5, 'Testar delivery próprio no fim de semana', 'Definir taxa de entrega, raio e embalagem e fazer um teste com amigos.', 'Mariana Castro Siqueira', 'Delivery, Ideia', 'ideias', 'media', NULL, '2026-11-14 11:07:40', '2026-11-15 11:07:40');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_layout`
--

CREATE TABLE `tb_layout` (
  `id_layout` int(11) NOT NULL,
  `id_gestor` int(11) NOT NULL,
  `nome_layout` varchar(255) NOT NULL,
  `id_cenario` varchar(50) NOT NULL,
  `largura_m` decimal(6,2) DEFAULT NULL,
  `comprimento_m` decimal(6,2) DEFAULT NULL,
  `dados_layout` longtext DEFAULT NULL,
  `data_criacao` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_layout`
--

INSERT INTO `tb_layout` (`id_layout`, `id_gestor`, `nome_layout`, `id_cenario`, `largura_m`, `comprimento_m`, `dados_layout`, `data_criacao`) VALUES
(4, 5, 'Cozinha Principal - Versão 1', 'ARQ-CUSTOM', 8.00, 5.50, '[{\"id\":\"item_1731700000001\",\"type\":\"geladeira\",\"x\":20,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700000002\",\"type\":\"geladeira\",\"x\":130,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700000003\",\"type\":\"armario\",\"x\":240,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700000004\",\"type\":\"armario\",\"x\":340,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700000005\",\"type\":\"bebedouro\",\"x\":440,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700000006\",\"type\":\"pia\",\"x\":510,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700000007\",\"type\":\"pia\",\"x\":650,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700000008\",\"type\":\"fogao\",\"x\":200,\"y\":440,\"rotation\":0},{\"id\":\"item_1731700000009\",\"type\":\"fogao\",\"x\":330,\"y\":440,\"rotation\":0},{\"id\":\"item_1731700000010\",\"type\":\"fritadeira\",\"x\":460,\"y\":440,\"rotation\":0},{\"id\":\"item_1731700000011\",\"type\":\"panelas\",\"x\":550,\"y\":460,\"rotation\":0},{\"id\":\"item_1731700000012\",\"type\":\"panelas\",\"x\":640,\"y\":460,\"rotation\":0},{\"id\":\"item_1731700000013\",\"type\":\"bancada\",\"x\":80,\"y\":240,\"rotation\":0},{\"id\":\"item_1731700000014\",\"type\":\"bancada\",\"x\":200,\"y\":240,\"rotation\":0},{\"id\":\"item_1731700000015\",\"type\":\"bancada\",\"x\":320,\"y\":240,\"rotation\":0},{\"id\":\"item_1731700000016\",\"type\":\"balcao\",\"x\":440,\"y\":240,\"rotation\":0},{\"id\":\"item_1731700000017\",\"type\":\"balcao\",\"x\":550,\"y\":240,\"rotation\":0},{\"id\":\"item_1731700000018\",\"type\":\"talheres\",\"x\":660,\"y\":255,\"rotation\":0},{\"id\":\"item_1731700000019\",\"type\":\"porta\",\"x\":20,\"y\":525,\"rotation\":0},{\"id\":\"item_1731700000020\",\"type\":\"janela\",\"x\":230,\"y\":0,\"rotation\":0},{\"id\":\"item_1731700000021\",\"type\":\"janela\",\"x\":480,\"y\":0,\"rotation\":0}]', '2026-10-27 11:07:40'),
(5, 5, 'Cozinha Principal - Versão 2 (mais espaço)', 'ARQ-CUSTOM', 9.00, 6.00, '[{\"id\":\"item_1731700001001\",\"type\":\"geladeira\",\"x\":20,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700001002\",\"type\":\"geladeira\",\"x\":130,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700001003\",\"type\":\"armario\",\"x\":240,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700001004\",\"type\":\"armario\",\"x\":340,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700001005\",\"type\":\"armario\",\"x\":440,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700001006\",\"type\":\"bebedouro\",\"x\":540,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700001007\",\"type\":\"pia\",\"x\":610,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700001008\",\"type\":\"pia\",\"x\":750,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700001009\",\"type\":\"fogao\",\"x\":140,\"y\":490,\"rotation\":0},{\"id\":\"item_1731700001010\",\"type\":\"fogao\",\"x\":270,\"y\":490,\"rotation\":0},{\"id\":\"item_1731700001011\",\"type\":\"fritadeira\",\"x\":400,\"y\":490,\"rotation\":0},{\"id\":\"item_1731700001012\",\"type\":\"fritadeira\",\"x\":490,\"y\":490,\"rotation\":0},{\"id\":\"item_1731700001013\",\"type\":\"panelas\",\"x\":580,\"y\":510,\"rotation\":0},{\"id\":\"item_1731700001014\",\"type\":\"panelas\",\"x\":670,\"y\":510,\"rotation\":0},{\"id\":\"item_1731700001015\",\"type\":\"bancada\",\"x\":60,\"y\":260,\"rotation\":0},{\"id\":\"item_1731700001016\",\"type\":\"bancada\",\"x\":180,\"y\":260,\"rotation\":0},{\"id\":\"item_1731700001017\",\"type\":\"bancada\",\"x\":300,\"y\":260,\"rotation\":0},{\"id\":\"item_1731700001018\",\"type\":\"bancada\",\"x\":420,\"y\":260,\"rotation\":0},{\"id\":\"item_1731700001019\",\"type\":\"balcao\",\"x\":540,\"y\":260,\"rotation\":0},{\"id\":\"item_1731700001020\",\"type\":\"balcao\",\"x\":650,\"y\":260,\"rotation\":0},{\"id\":\"item_1731700001021\",\"type\":\"talheres\",\"x\":760,\"y\":275,\"rotation\":0},{\"id\":\"item_1731700001022\",\"type\":\"porta\",\"x\":20,\"y\":575,\"rotation\":0},{\"id\":\"item_1731700001023\",\"type\":\"porta\",\"x\":790,\"y\":575,\"rotation\":0},{\"id\":\"item_1731700001024\",\"type\":\"janela\",\"x\":260,\"y\":0,\"rotation\":0},{\"id\":\"item_1731700001025\",\"type\":\"janela\",\"x\":550,\"y\":0,\"rotation\":0}]', '2026-10-28 11:07:40'),
(6, 5, 'Cozinha Industrial', 'ARQ-CUSTOM', 12.00, 7.00, '[{\"id\":\"item_1731700002001\",\"type\":\"geladeira\",\"x\":40,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700002002\",\"type\":\"geladeira\",\"x\":150,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700002003\",\"type\":\"geladeira\",\"x\":260,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700002004\",\"type\":\"armario\",\"x\":370,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700002005\",\"type\":\"armario\",\"x\":470,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700002006\",\"type\":\"armario\",\"x\":570,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700002007\",\"type\":\"bebedouro\",\"x\":670,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700002008\",\"type\":\"pia\",\"x\":740,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700002010\",\"type\":\"pia\",\"x\":1020,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700002011\",\"type\":\"fogao\",\"x\":160,\"y\":590,\"rotation\":0},{\"id\":\"item_1731700002012\",\"type\":\"fogao\",\"x\":290,\"y\":590,\"rotation\":0},{\"id\":\"item_1731700002013\",\"type\":\"fogao\",\"x\":420,\"y\":590,\"rotation\":0},{\"id\":\"item_1731700002014\",\"type\":\"fogao\",\"x\":550,\"y\":590,\"rotation\":0},{\"id\":\"item_1731700002015\",\"type\":\"fritadeira\",\"x\":680,\"y\":590,\"rotation\":0},{\"id\":\"item_1731700002016\",\"type\":\"fritadeira\",\"x\":770,\"y\":590,\"rotation\":0},{\"id\":\"item_1731700002017\",\"type\":\"fritadeira\",\"x\":860,\"y\":590,\"rotation\":0},{\"id\":\"item_1731700002019\",\"type\":\"bancada\",\"x\":280,\"y\":250,\"rotation\":0},{\"id\":\"item_1731700002020\",\"type\":\"bancada\",\"x\":400,\"y\":250,\"rotation\":0},{\"id\":\"item_1731700002021\",\"type\":\"bancada\",\"x\":520,\"y\":250,\"rotation\":0},{\"id\":\"item_1731700002022\",\"type\":\"balcao\",\"x\":640,\"y\":250,\"rotation\":0},{\"id\":\"item_1731700002023\",\"type\":\"talheres\",\"x\":750,\"y\":265,\"rotation\":0},{\"id\":\"item_1731700002024\",\"type\":\"panelas\",\"x\":830,\"y\":260,\"rotation\":0},{\"id\":\"item_1731700002025\",\"type\":\"bancada\",\"x\":290,\"y\":380,\"rotation\":0},{\"id\":\"item_1731700002026\",\"type\":\"bancada\",\"x\":410,\"y\":380,\"rotation\":0},{\"id\":\"item_1731700002027\",\"type\":\"balcao\",\"x\":530,\"y\":380,\"rotation\":0},{\"id\":\"item_1731700002028\",\"type\":\"balcao\",\"x\":640,\"y\":380,\"rotation\":0},{\"id\":\"item_1731700002029\",\"type\":\"talheres\",\"x\":750,\"y\":395,\"rotation\":0},{\"id\":\"item_1731700002030\",\"type\":\"panelas\",\"x\":830,\"y\":390,\"rotation\":0},{\"id\":\"item_1731700002031\",\"type\":\"porta\",\"x\":20,\"y\":675,\"rotation\":0},{\"id\":\"item_1731700002032\",\"type\":\"porta\",\"x\":1090,\"y\":675,\"rotation\":0},{\"id\":\"item_1731700002033\",\"type\":\"janela\",\"x\":260,\"y\":0,\"rotation\":0},{\"id\":\"item_1731700002034\",\"type\":\"janela\",\"x\":560,\"y\":0,\"rotation\":0},{\"id\":\"item_1731700002035\",\"type\":\"janela\",\"x\":840,\"y\":0,\"rotation\":0}]', '2026-10-29 11:07:40'),
(7, 5, 'Cozinha Compacta - Delivery', 'ARQ-CUSTOM', 5.00, 5.00, '[{\"id\":\"item_1731700003001\",\"type\":\"geladeira\",\"x\":80,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700003002\",\"type\":\"armario\",\"x\":190,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700003003\",\"type\":\"pia\",\"x\":290,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700003004\",\"type\":\"fogao\",\"x\":160,\"y\":390,\"rotation\":0},{\"id\":\"item_1731700003005\",\"type\":\"fritadeira\",\"x\":290,\"y\":390,\"rotation\":0},{\"id\":\"item_1731700003006\",\"type\":\"panelas\",\"x\":380,\"y\":410,\"rotation\":0},{\"id\":\"item_1731700003007\",\"type\":\"bancada\",\"x\":40,\"y\":210,\"rotation\":0},{\"id\":\"item_1731700003008\",\"type\":\"bancada\",\"x\":160,\"y\":210,\"rotation\":0},{\"id\":\"item_1731700003009\",\"type\":\"balcao\",\"x\":280,\"y\":210,\"rotation\":0},{\"id\":\"item_1731700003010\",\"type\":\"talheres\",\"x\":390,\"y\":225,\"rotation\":0},{\"id\":\"item_1731700003011\",\"type\":\"porta\",\"x\":20,\"y\":475,\"rotation\":0},{\"id\":\"item_1731700003012\",\"type\":\"janela\",\"x\":200,\"y\":0,\"rotation\":0}]', '2026-10-30 11:07:40'),
(8, 5, 'Hamburgueria - Chapa e Fritura', 'ARQ-CUSTOM', 7.00, 5.00, '[{\"id\":\"item_1731700004001\",\"type\":\"geladeira\",\"x\":180,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700004002\",\"type\":\"geladeira\",\"x\":290,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700004003\",\"type\":\"pia\",\"x\":400,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700004004\",\"type\":\"fogao\",\"x\":170,\"y\":390,\"rotation\":0},{\"id\":\"item_1731700004005\",\"type\":\"fritadeira\",\"x\":300,\"y\":390,\"rotation\":0},{\"id\":\"item_1731700004006\",\"type\":\"fritadeira\",\"x\":390,\"y\":390,\"rotation\":0},{\"id\":\"item_1731700004007\",\"type\":\"fritadeira\",\"x\":480,\"y\":390,\"rotation\":0},{\"id\":\"item_1731700004008\",\"type\":\"panelas\",\"x\":570,\"y\":410,\"rotation\":0},{\"id\":\"item_1731700004009\",\"type\":\"bancada\",\"x\":20,\"y\":210,\"rotation\":0},{\"id\":\"item_1731700004010\",\"type\":\"bancada\",\"x\":140,\"y\":210,\"rotation\":0},{\"id\":\"item_1731700004011\",\"type\":\"bancada\",\"x\":260,\"y\":210,\"rotation\":0},{\"id\":\"item_1731700004012\",\"type\":\"balcao\",\"x\":380,\"y\":210,\"rotation\":0},{\"id\":\"item_1731700004013\",\"type\":\"balcao\",\"x\":490,\"y\":210,\"rotation\":0},{\"id\":\"item_1731700004014\",\"type\":\"talheres\",\"x\":600,\"y\":225,\"rotation\":0},{\"id\":\"item_1731700004015\",\"type\":\"porta\",\"x\":20,\"y\":475,\"rotation\":0},{\"id\":\"item_1731700004016\",\"type\":\"janela\",\"x\":300,\"y\":0,\"rotation\":0}]', '2026-10-31 11:07:40'),
(9, 5, 'Praça de Fritura', 'ARQ-CUSTOM', 6.50, 4.50, '[{\"id\":\"item_1731700005001\",\"type\":\"geladeira\",\"x\":40,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700005002\",\"type\":\"pia\",\"x\":150,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700005003\",\"type\":\"bancada\",\"x\":290,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700005004\",\"type\":\"balcao\",\"x\":410,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700005005\",\"type\":\"panelas\",\"x\":520,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700005006\",\"type\":\"bancada\",\"x\":150,\"y\":340,\"rotation\":0},{\"id\":\"item_1731700005007\",\"type\":\"fritadeira\",\"x\":270,\"y\":340,\"rotation\":0},{\"id\":\"item_1731700005008\",\"type\":\"fritadeira\",\"x\":360,\"y\":340,\"rotation\":0},{\"id\":\"item_1731700005009\",\"type\":\"fritadeira\",\"x\":450,\"y\":340,\"rotation\":0},{\"id\":\"item_1731700005010\",\"type\":\"fritadeira\",\"x\":540,\"y\":340,\"rotation\":0},{\"id\":\"item_1731700005011\",\"type\":\"porta\",\"x\":20,\"y\":425,\"rotation\":0},{\"id\":\"item_1731700005012\",\"type\":\"janela\",\"x\":280,\"y\":0,\"rotation\":0}]', '2026-11-01 11:07:40'),
(10, 5, 'Setor de Grelhados e Espetinhos', 'ARQ-CUSTOM', 7.00, 5.00, '[{\"id\":\"item_1731700006001\",\"type\":\"geladeira\",\"x\":180,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700006002\",\"type\":\"armario\",\"x\":290,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700006003\",\"type\":\"pia\",\"x\":390,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700006004\",\"type\":\"fogao\",\"x\":180,\"y\":390,\"rotation\":0},{\"id\":\"item_1731700006005\",\"type\":\"fogao\",\"x\":310,\"y\":390,\"rotation\":0},{\"id\":\"item_1731700006006\",\"type\":\"fogao\",\"x\":440,\"y\":390,\"rotation\":0},{\"id\":\"item_1731700006007\",\"type\":\"panelas\",\"x\":570,\"y\":410,\"rotation\":0},{\"id\":\"item_1731700006008\",\"type\":\"bancada\",\"x\":40,\"y\":210,\"rotation\":0},{\"id\":\"item_1731700006009\",\"type\":\"bancada\",\"x\":160,\"y\":210,\"rotation\":0},{\"id\":\"item_1731700006010\",\"type\":\"bancada\",\"x\":280,\"y\":210,\"rotation\":0},{\"id\":\"item_1731700006011\",\"type\":\"balcao\",\"x\":400,\"y\":210,\"rotation\":0},{\"id\":\"item_1731700006012\",\"type\":\"talheres\",\"x\":510,\"y\":225,\"rotation\":0},{\"id\":\"item_1731700006013\",\"type\":\"panelas\",\"x\":590,\"y\":220,\"rotation\":0},{\"id\":\"item_1731700006014\",\"type\":\"porta\",\"x\":20,\"y\":475,\"rotation\":0},{\"id\":\"item_1731700006015\",\"type\":\"janela\",\"x\":200,\"y\":0,\"rotation\":0},{\"id\":\"item_1731700006016\",\"type\":\"janela\",\"x\":420,\"y\":0,\"rotation\":0}]', '2026-11-02 11:07:40'),
(11, 5, 'Área de Preparo Frio (saladas)', 'ARQ-CUSTOM', 6.00, 5.00, '[{\"id\":\"item_1731700007001\",\"type\":\"geladeira\",\"x\":20,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700007002\",\"type\":\"geladeira\",\"x\":130,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700007003\",\"type\":\"armario\",\"x\":240,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700007004\",\"type\":\"armario\",\"x\":340,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700007005\",\"type\":\"bebedouro\",\"x\":440,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700007006\",\"type\":\"talheres\",\"x\":510,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700007007\",\"type\":\"bancada\",\"x\":190,\"y\":390,\"rotation\":0},{\"id\":\"item_1731700007008\",\"type\":\"bancada\",\"x\":310,\"y\":390,\"rotation\":0},{\"id\":\"item_1731700007009\",\"type\":\"balcao\",\"x\":430,\"y\":390,\"rotation\":0},{\"id\":\"item_1731700007010\",\"type\":\"pia\",\"x\":40,\"y\":210,\"rotation\":0},{\"id\":\"item_1731700007011\",\"type\":\"pia\",\"x\":180,\"y\":210,\"rotation\":0},{\"id\":\"item_1731700007012\",\"type\":\"bancada\",\"x\":320,\"y\":210,\"rotation\":0},{\"id\":\"item_1731700007013\",\"type\":\"bancada\",\"x\":440,\"y\":210,\"rotation\":0},{\"id\":\"item_1731700007014\",\"type\":\"porta\",\"x\":20,\"y\":475,\"rotation\":0},{\"id\":\"item_1731700007015\",\"type\":\"janela\",\"x\":260,\"y\":0,\"rotation\":0}]', '2026-11-03 11:07:40'),
(12, 5, 'Copa e Bar', 'ARQ-CUSTOM', 6.00, 4.00, '[{\"id\":\"item_1731700008001\",\"type\":\"geladeira\",\"x\":20,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700008002\",\"type\":\"geladeira\",\"x\":130,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700008003\",\"type\":\"armario\",\"x\":240,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700008004\",\"type\":\"bebedouro\",\"x\":340,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700008005\",\"type\":\"bebedouro\",\"x\":410,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700008006\",\"type\":\"balcao\",\"x\":480,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700008007\",\"type\":\"pia\",\"x\":140,\"y\":290,\"rotation\":0},{\"id\":\"item_1731700008008\",\"type\":\"bancada\",\"x\":280,\"y\":290,\"rotation\":0},{\"id\":\"item_1731700008009\",\"type\":\"balcao\",\"x\":400,\"y\":290,\"rotation\":0},{\"id\":\"item_1731700008010\",\"type\":\"talheres\",\"x\":510,\"y\":320,\"rotation\":0},{\"id\":\"item_1731700008011\",\"type\":\"porta\",\"x\":20,\"y\":375,\"rotation\":0},{\"id\":\"item_1731700008012\",\"type\":\"janela\",\"x\":260,\"y\":0,\"rotation\":0}]', '2026-11-04 11:07:40'),
(13, 5, 'Estação de Sobremesas', 'ARQ-CUSTOM', 6.50, 3.50, '[{\"id\":\"item_1731700009001\",\"type\":\"geladeira\",\"x\":40,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700009002\",\"type\":\"geladeira\",\"x\":150,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700009003\",\"type\":\"armario\",\"x\":260,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700009004\",\"type\":\"pia\",\"x\":360,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700009005\",\"type\":\"bancada\",\"x\":500,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700009006\",\"type\":\"bancada\",\"x\":180,\"y\":240,\"rotation\":0},{\"id\":\"item_1731700009007\",\"type\":\"talheres\",\"x\":300,\"y\":270,\"rotation\":0},{\"id\":\"item_1731700009008\",\"type\":\"fogao\",\"x\":380,\"y\":240,\"rotation\":0},{\"id\":\"item_1731700009009\",\"type\":\"panelas\",\"x\":510,\"y\":260,\"rotation\":0},{\"id\":\"item_1731700009010\",\"type\":\"porta\",\"x\":20,\"y\":325,\"rotation\":0},{\"id\":\"item_1731700009011\",\"type\":\"janela\",\"x\":280,\"y\":0,\"rotation\":0}]', '2026-11-05 11:07:40'),
(14, 5, 'Layout de Inauguração', 'ARQ-CUSTOM', 10.00, 6.00, '[{\"id\":\"item_1731700010001\",\"type\":\"geladeira\",\"x\":20,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700010002\",\"type\":\"geladeira\",\"x\":130,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700010003\",\"type\":\"geladeira\",\"x\":240,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700010004\",\"type\":\"armario\",\"x\":350,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700010005\",\"type\":\"armario\",\"x\":450,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700010006\",\"type\":\"bebedouro\",\"x\":550,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700010007\",\"type\":\"pia\",\"x\":612,\"y\":34,\"rotation\":0},{\"id\":\"item_1731700010008\",\"type\":\"pia\",\"x\":746,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700010009\",\"type\":\"panelas\",\"x\":877,\"y\":36,\"rotation\":0},{\"id\":\"item_1731700010010\",\"type\":\"fogao\",\"x\":180,\"y\":490,\"rotation\":0},{\"id\":\"item_1731700010011\",\"type\":\"fogao\",\"x\":310,\"y\":490,\"rotation\":0},{\"id\":\"item_1731700010012\",\"type\":\"fogao\",\"x\":440,\"y\":490,\"rotation\":0},{\"id\":\"item_1731700010013\",\"type\":\"fritadeira\",\"x\":570,\"y\":490,\"rotation\":0},{\"id\":\"item_1731700010014\",\"type\":\"fritadeira\",\"x\":660,\"y\":490,\"rotation\":0},{\"id\":\"item_1731700010015\",\"type\":\"panelas\",\"x\":750,\"y\":510,\"rotation\":0},{\"id\":\"item_1731700010016\",\"type\":\"bancada\",\"x\":20,\"y\":260,\"rotation\":0},{\"id\":\"item_1731700010017\",\"type\":\"bancada\",\"x\":140,\"y\":260,\"rotation\":0},{\"id\":\"item_1731700010018\",\"type\":\"bancada\",\"x\":260,\"y\":260,\"rotation\":0},{\"id\":\"item_1731700010019\",\"type\":\"bancada\",\"x\":380,\"y\":260,\"rotation\":0},{\"id\":\"item_1731700010020\",\"type\":\"balcao\",\"x\":500,\"y\":260,\"rotation\":0},{\"id\":\"item_1731700010021\",\"type\":\"balcao\",\"x\":610,\"y\":260,\"rotation\":0},{\"id\":\"item_1731700010022\",\"type\":\"balcao\",\"x\":720,\"y\":260,\"rotation\":0},{\"id\":\"item_1731700010023\",\"type\":\"talheres\",\"x\":830,\"y\":275,\"rotation\":0},{\"id\":\"item_1731700010025\",\"type\":\"porta\",\"x\":20,\"y\":575,\"rotation\":0},{\"id\":\"item_1731700010026\",\"type\":\"porta\",\"x\":890,\"y\":575,\"rotation\":0},{\"id\":\"item_1731700010027\",\"type\":\"janela\",\"x\":220,\"y\":0,\"rotation\":0},{\"id\":\"item_1731700010028\",\"type\":\"janela\",\"x\":460,\"y\":0,\"rotation\":0},{\"id\":\"item_1731700010029\",\"type\":\"janela\",\"x\":700,\"y\":0,\"rotation\":0}]', '2026-11-06 11:07:40'),
(15, 5, 'Cozinha com Ilha Central', 'ARQ-CUSTOM', 9.00, 6.50, '[{\"id\":\"item_1731700011001\",\"type\":\"geladeira\",\"x\":100,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700011002\",\"type\":\"geladeira\",\"x\":210,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700011003\",\"type\":\"armario\",\"x\":320,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700011004\",\"type\":\"armario\",\"x\":420,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700011005\",\"type\":\"pia\",\"x\":520,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700011006\",\"type\":\"pia\",\"x\":660,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700011007\",\"type\":\"fogao\",\"x\":250,\"y\":540,\"rotation\":0},{\"id\":\"item_1731700011008\",\"type\":\"fogao\",\"x\":380,\"y\":540,\"rotation\":0},{\"id\":\"item_1731700011009\",\"type\":\"fritadeira\",\"x\":510,\"y\":540,\"rotation\":0},{\"id\":\"item_1731700011010\",\"type\":\"panelas\",\"x\":600,\"y\":560,\"rotation\":0},{\"id\":\"item_1731700011011\",\"type\":\"panelas\",\"x\":690,\"y\":560,\"rotation\":0},{\"id\":\"item_1731700011012\",\"type\":\"bancada\",\"x\":60,\"y\":290,\"rotation\":0},{\"id\":\"item_1731700011013\",\"type\":\"bancada\",\"x\":180,\"y\":290,\"rotation\":0},{\"id\":\"item_1731700011014\",\"type\":\"bancada\",\"x\":300,\"y\":290,\"rotation\":0},{\"id\":\"item_1731700011015\",\"type\":\"balcao\",\"x\":420,\"y\":290,\"rotation\":0},{\"id\":\"item_1731700011016\",\"type\":\"balcao\",\"x\":530,\"y\":290,\"rotation\":0},{\"id\":\"item_1731700011017\",\"type\":\"balcao\",\"x\":640,\"y\":290,\"rotation\":0},{\"id\":\"item_1731700011018\",\"type\":\"balcao\",\"x\":750,\"y\":290,\"rotation\":0},{\"id\":\"item_1731700011019\",\"type\":\"porta\",\"x\":20,\"y\":625,\"rotation\":0},{\"id\":\"item_1731700011020\",\"type\":\"janela\",\"x\":260,\"y\":0,\"rotation\":0},{\"id\":\"item_1731700011021\",\"type\":\"janela\",\"x\":550,\"y\":0,\"rotation\":0}]', '2026-11-07 11:07:40'),
(16, 5, 'Cozinha em Linha (corredor)', 'ARQ-CUSTOM', 11.00, 5.00, '[{\"id\":\"item_1731700012001\",\"type\":\"geladeira\",\"x\":200,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700012002\",\"type\":\"geladeira\",\"x\":310,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700012003\",\"type\":\"armario\",\"x\":420,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700012004\",\"type\":\"armario\",\"x\":520,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700012005\",\"type\":\"pia\",\"x\":620,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700012006\",\"type\":\"pia\",\"x\":760,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700012007\",\"type\":\"fogao\",\"x\":220,\"y\":390,\"rotation\":0},{\"id\":\"item_1731700012008\",\"type\":\"fogao\",\"x\":350,\"y\":390,\"rotation\":0},{\"id\":\"item_1731700012009\",\"type\":\"fogao\",\"x\":480,\"y\":390,\"rotation\":0},{\"id\":\"item_1731700012010\",\"type\":\"fritadeira\",\"x\":610,\"y\":390,\"rotation\":0},{\"id\":\"item_1731700012011\",\"type\":\"fritadeira\",\"x\":700,\"y\":390,\"rotation\":0},{\"id\":\"item_1731700012012\",\"type\":\"panelas\",\"x\":790,\"y\":410,\"rotation\":0},{\"id\":\"item_1731700012013\",\"type\":\"bancada\",\"x\":160,\"y\":210,\"rotation\":0},{\"id\":\"item_1731700012014\",\"type\":\"bancada\",\"x\":280,\"y\":210,\"rotation\":0},{\"id\":\"item_1731700012015\",\"type\":\"bancada\",\"x\":400,\"y\":210,\"rotation\":0},{\"id\":\"item_1731700012016\",\"type\":\"bancada\",\"x\":520,\"y\":210,\"rotation\":0},{\"id\":\"item_1731700012017\",\"type\":\"balcao\",\"x\":640,\"y\":210,\"rotation\":0},{\"id\":\"item_1731700012018\",\"type\":\"balcao\",\"x\":750,\"y\":210,\"rotation\":0},{\"id\":\"item_1731700012019\",\"type\":\"talheres\",\"x\":860,\"y\":225,\"rotation\":0},{\"id\":\"item_1731700012020\",\"type\":\"porta\",\"x\":20,\"y\":475,\"rotation\":0},{\"id\":\"item_1731700012021\",\"type\":\"porta\",\"x\":990,\"y\":475,\"rotation\":0},{\"id\":\"item_1731700012022\",\"type\":\"janela\",\"x\":330,\"y\":0,\"rotation\":0},{\"id\":\"item_1731700012023\",\"type\":\"janela\",\"x\":680,\"y\":0,\"rotation\":0}]', '2026-11-08 11:07:40'),
(17, 5, 'Cozinha em L', 'ARQ-CUSTOM', 8.00, 6.00, '[{\"id\":\"item_1731700013001\",\"type\":\"geladeira\",\"x\":40,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700013002\",\"type\":\"geladeira\",\"x\":150,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700013003\",\"type\":\"armario\",\"x\":260,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700013004\",\"type\":\"armario\",\"x\":360,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700013005\",\"type\":\"armario\",\"x\":460,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700013006\",\"type\":\"bebedouro\",\"x\":560,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700013007\",\"type\":\"pia\",\"x\":630,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700013008\",\"type\":\"fogao\",\"x\":200,\"y\":490,\"rotation\":0},{\"id\":\"item_1731700013009\",\"type\":\"fogao\",\"x\":330,\"y\":490,\"rotation\":0},{\"id\":\"item_1731700013010\",\"type\":\"fritadeira\",\"x\":460,\"y\":490,\"rotation\":0},{\"id\":\"item_1731700013011\",\"type\":\"panelas\",\"x\":550,\"y\":510,\"rotation\":0},{\"id\":\"item_1731700013012\",\"type\":\"panelas\",\"x\":640,\"y\":510,\"rotation\":0},{\"id\":\"item_1731700013013\",\"type\":\"pia\",\"x\":40,\"y\":260,\"rotation\":0},{\"id\":\"item_1731700013014\",\"type\":\"bancada\",\"x\":180,\"y\":260,\"rotation\":0},{\"id\":\"item_1731700013015\",\"type\":\"bancada\",\"x\":300,\"y\":260,\"rotation\":0},{\"id\":\"item_1731700013016\",\"type\":\"bancada\",\"x\":420,\"y\":260,\"rotation\":0},{\"id\":\"item_1731700013017\",\"type\":\"balcao\",\"x\":540,\"y\":260,\"rotation\":0},{\"id\":\"item_1731700013018\",\"type\":\"balcao\",\"x\":650,\"y\":260,\"rotation\":0},{\"id\":\"item_1731700013019\",\"type\":\"porta\",\"x\":20,\"y\":575,\"rotation\":0},{\"id\":\"item_1731700013020\",\"type\":\"janela\",\"x\":230,\"y\":0,\"rotation\":0},{\"id\":\"item_1731700013021\",\"type\":\"janela\",\"x\":480,\"y\":0,\"rotation\":0}]', '2026-11-09 11:07:40'),
(18, 5, 'Despensa e Estoque Seco', 'ARQ-CUSTOM', 6.00, 5.00, '[{\"id\":\"item_1731700014001\",\"type\":\"geladeira\",\"x\":40,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700014002\",\"type\":\"geladeira\",\"x\":150,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700014003\",\"type\":\"armario\",\"x\":260,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700014004\",\"type\":\"armario\",\"x\":360,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700014005\",\"type\":\"armario\",\"x\":460,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700014006\",\"type\":\"balcao\",\"x\":260,\"y\":390,\"rotation\":0},{\"id\":\"item_1731700014007\",\"type\":\"balcao\",\"x\":370,\"y\":390,\"rotation\":0},{\"id\":\"item_1731700014008\",\"type\":\"armario\",\"x\":40,\"y\":215,\"rotation\":0},{\"id\":\"item_1731700014009\",\"type\":\"armario\",\"x\":140,\"y\":215,\"rotation\":0},{\"id\":\"item_1731700014010\",\"type\":\"bebedouro\",\"x\":240,\"y\":220,\"rotation\":0},{\"id\":\"item_1731700014011\",\"type\":\"pia\",\"x\":310,\"y\":210,\"rotation\":0},{\"id\":\"item_1731700014012\",\"type\":\"bancada\",\"x\":450,\"y\":210,\"rotation\":0},{\"id\":\"item_1731700014013\",\"type\":\"porta\",\"x\":20,\"y\":475,\"rotation\":0},{\"id\":\"item_1731700014014\",\"type\":\"janela\",\"x\":260,\"y\":0,\"rotation\":0}]', '2026-11-10 11:07:40'),
(19, 5, 'Cozinha Pequena - Planta Atual', 'ARQ-CUSTOM', 6.50, 5.00, '[{\"id\":\"item_1731700015001\",\"type\":\"geladeira\",\"x\":70,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700015002\",\"type\":\"armario\",\"x\":180,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700015003\",\"type\":\"armario\",\"x\":280,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700015004\",\"type\":\"bebedouro\",\"x\":380,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700015005\",\"type\":\"pia\",\"x\":450,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700015006\",\"type\":\"fogao\",\"x\":170,\"y\":390,\"rotation\":0},{\"id\":\"item_1731700015007\",\"type\":\"fogao\",\"x\":300,\"y\":390,\"rotation\":0},{\"id\":\"item_1731700015008\",\"type\":\"fritadeira\",\"x\":430,\"y\":390,\"rotation\":0},{\"id\":\"item_1731700015009\",\"type\":\"panelas\",\"x\":520,\"y\":410,\"rotation\":0},{\"id\":\"item_1731700015010\",\"type\":\"bancada\",\"x\":120,\"y\":210,\"rotation\":0},{\"id\":\"item_1731700015011\",\"type\":\"bancada\",\"x\":240,\"y\":210,\"rotation\":0},{\"id\":\"item_1731700015012\",\"type\":\"balcao\",\"x\":360,\"y\":210,\"rotation\":0},{\"id\":\"item_1731700015013\",\"type\":\"talheres\",\"x\":470,\"y\":225,\"rotation\":0},{\"id\":\"item_1731700015014\",\"type\":\"porta\",\"x\":20,\"y\":475,\"rotation\":0},{\"id\":\"item_1731700015015\",\"type\":\"janela\",\"x\":280,\"y\":0,\"rotation\":0}]', '2026-11-11 11:07:40'),
(20, 5, 'Reforma - Opção A (mais bancadas)', 'ARQ-CUSTOM', 8.50, 5.50, '[{\"id\":\"item_1731700016001\",\"type\":\"geladeira\",\"x\":40,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700016002\",\"type\":\"geladeira\",\"x\":150,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700016003\",\"type\":\"armario\",\"x\":260,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700016004\",\"type\":\"armario\",\"x\":360,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700016005\",\"type\":\"pia\",\"x\":460,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700016006\",\"type\":\"pia\",\"x\":600,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700016007\",\"type\":\"talheres\",\"x\":740,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700016008\",\"type\":\"fogao\",\"x\":220,\"y\":440,\"rotation\":0},{\"id\":\"item_1731700016009\",\"type\":\"fogao\",\"x\":350,\"y\":440,\"rotation\":0},{\"id\":\"item_1731700016010\",\"type\":\"fritadeira\",\"x\":480,\"y\":440,\"rotation\":0},{\"id\":\"item_1731700016011\",\"type\":\"panelas\",\"x\":570,\"y\":460,\"rotation\":0},{\"id\":\"item_1731700016012\",\"type\":\"panelas\",\"x\":660,\"y\":460,\"rotation\":0},{\"id\":\"item_1731700016013\",\"type\":\"bancada\",\"x\":20,\"y\":240,\"rotation\":0},{\"id\":\"item_1731700016014\",\"type\":\"bancada\",\"x\":140,\"y\":240,\"rotation\":0},{\"id\":\"item_1731700016015\",\"type\":\"bancada\",\"x\":260,\"y\":240,\"rotation\":0},{\"id\":\"item_1731700016016\",\"type\":\"bancada\",\"x\":380,\"y\":240,\"rotation\":0},{\"id\":\"item_1731700016017\",\"type\":\"bancada\",\"x\":500,\"y\":240,\"rotation\":0},{\"id\":\"item_1731700016018\",\"type\":\"balcao\",\"x\":620,\"y\":240,\"rotation\":0},{\"id\":\"item_1731700016019\",\"type\":\"balcao\",\"x\":730,\"y\":240,\"rotation\":0},{\"id\":\"item_1731700016020\",\"type\":\"porta\",\"x\":20,\"y\":525,\"rotation\":0},{\"id\":\"item_1731700016021\",\"type\":\"janela\",\"x\":240,\"y\":0,\"rotation\":0},{\"id\":\"item_1731700016022\",\"type\":\"janela\",\"x\":520,\"y\":0,\"rotation\":0}]', '2026-11-12 11:07:40'),
(21, 5, 'Reforma - Opção B (mais fogões)', 'ARQ-CUSTOM', 8.50, 5.50, '[{\"id\":\"item_1731700017001\",\"type\":\"geladeira\",\"x\":80,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700017002\",\"type\":\"geladeira\",\"x\":190,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700017003\",\"type\":\"armario\",\"x\":300,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700017004\",\"type\":\"armario\",\"x\":400,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700017005\",\"type\":\"pia\",\"x\":500,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700017006\",\"type\":\"pia\",\"x\":640,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700017007\",\"type\":\"fogao\",\"x\":140,\"y\":440,\"rotation\":0},{\"id\":\"item_1731700017008\",\"type\":\"fogao\",\"x\":270,\"y\":440,\"rotation\":0},{\"id\":\"item_1731700017009\",\"type\":\"fogao\",\"x\":400,\"y\":440,\"rotation\":0},{\"id\":\"item_1731700017010\",\"type\":\"fogao\",\"x\":530,\"y\":440,\"rotation\":0},{\"id\":\"item_1731700017011\",\"type\":\"fritadeira\",\"x\":660,\"y\":440,\"rotation\":0},{\"id\":\"item_1731700017012\",\"type\":\"fritadeira\",\"x\":750,\"y\":440,\"rotation\":0},{\"id\":\"item_1731700017013\",\"type\":\"bancada\",\"x\":20,\"y\":240,\"rotation\":0},{\"id\":\"item_1731700017014\",\"type\":\"bancada\",\"x\":140,\"y\":240,\"rotation\":0},{\"id\":\"item_1731700017015\",\"type\":\"balcao\",\"x\":260,\"y\":240,\"rotation\":0},{\"id\":\"item_1731700017016\",\"type\":\"balcao\",\"x\":370,\"y\":240,\"rotation\":0},{\"id\":\"item_1731700017017\",\"type\":\"talheres\",\"x\":480,\"y\":255,\"rotation\":0},{\"id\":\"item_1731700017018\",\"type\":\"panelas\",\"x\":560,\"y\":250,\"rotation\":0},{\"id\":\"item_1731700017019\",\"type\":\"panelas\",\"x\":650,\"y\":250,\"rotation\":0},{\"id\":\"item_1731700017020\",\"type\":\"panelas\",\"x\":740,\"y\":250,\"rotation\":0},{\"id\":\"item_1731700017021\",\"type\":\"porta\",\"x\":20,\"y\":525,\"rotation\":0},{\"id\":\"item_1731700017022\",\"type\":\"janela\",\"x\":240,\"y\":0,\"rotation\":0},{\"id\":\"item_1731700017023\",\"type\":\"janela\",\"x\":520,\"y\":0,\"rotation\":0}]', '2026-11-13 11:07:40'),
(22, 5, 'Praça de Alimentação - Cozinha de Apoio', 'ARQ-CUSTOM', 10.00, 5.00, '[{\"id\":\"item_1731700018001\",\"type\":\"geladeira\",\"x\":30,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700018002\",\"type\":\"geladeira\",\"x\":140,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700018003\",\"type\":\"geladeira\",\"x\":250,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700018004\",\"type\":\"armario\",\"x\":360,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700018005\",\"type\":\"armario\",\"x\":460,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700018006\",\"type\":\"bebedouro\",\"x\":560,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700018007\",\"type\":\"bebedouro\",\"x\":630,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700018008\",\"type\":\"pia\",\"x\":689,\"y\":36,\"rotation\":0},{\"id\":\"item_1731700018009\",\"type\":\"pia\",\"x\":819,\"y\":36,\"rotation\":0},{\"id\":\"item_1731700018010\",\"type\":\"fogao\",\"x\":200,\"y\":390,\"rotation\":0},{\"id\":\"item_1731700018011\",\"type\":\"fogao\",\"x\":330,\"y\":390,\"rotation\":0},{\"id\":\"item_1731700018012\",\"type\":\"fritadeira\",\"x\":460,\"y\":390,\"rotation\":0},{\"id\":\"item_1731700018013\",\"type\":\"fritadeira\",\"x\":550,\"y\":390,\"rotation\":0},{\"id\":\"item_1731700018014\",\"type\":\"panelas\",\"x\":640,\"y\":410,\"rotation\":0},{\"id\":\"item_1731700018015\",\"type\":\"panelas\",\"x\":730,\"y\":410,\"rotation\":0},{\"id\":\"item_1731700018016\",\"type\":\"bancada\",\"x\":80,\"y\":210,\"rotation\":0},{\"id\":\"item_1731700018017\",\"type\":\"bancada\",\"x\":200,\"y\":210,\"rotation\":0},{\"id\":\"item_1731700018018\",\"type\":\"bancada\",\"x\":320,\"y\":210,\"rotation\":0},{\"id\":\"item_1731700018019\",\"type\":\"balcao\",\"x\":440,\"y\":210,\"rotation\":0},{\"id\":\"item_1731700018020\",\"type\":\"balcao\",\"x\":550,\"y\":210,\"rotation\":0},{\"id\":\"item_1731700018021\",\"type\":\"balcao\",\"x\":660,\"y\":210,\"rotation\":0},{\"id\":\"item_1731700018022\",\"type\":\"talheres\",\"x\":770,\"y\":225,\"rotation\":0},{\"id\":\"item_1731700018023\",\"type\":\"talheres\",\"x\":850,\"y\":225,\"rotation\":0},{\"id\":\"item_1731700018024\",\"type\":\"porta\",\"x\":20,\"y\":475,\"rotation\":0},{\"id\":\"item_1731700018025\",\"type\":\"porta\",\"x\":852,\"y\":475,\"rotation\":0},{\"id\":\"item_1731700018026\",\"type\":\"janela\",\"x\":300,\"y\":0,\"rotation\":0},{\"id\":\"item_1731700018027\",\"type\":\"janela\",\"x\":620,\"y\":0,\"rotation\":0}]', '2026-11-14 11:07:40'),
(23, 5, 'Cozinha de Eventos', 'ARQ-CUSTOM', 12.00, 8.00, '[{\"id\":\"item_1731700019001\",\"type\":\"geladeira\",\"x\":60,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700019002\",\"type\":\"geladeira\",\"x\":170,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700019003\",\"type\":\"geladeira\",\"x\":280,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700019004\",\"type\":\"geladeira\",\"x\":390,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700019005\",\"type\":\"armario\",\"x\":500,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700019006\",\"type\":\"armario\",\"x\":600,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700019007\",\"type\":\"armario\",\"x\":700,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700019008\",\"type\":\"armario\",\"x\":800,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700019009\",\"type\":\"bebedouro\",\"x\":900,\"y\":35,\"rotation\":0},{\"id\":\"item_1731700019011\",\"type\":\"fogao\",\"x\":160,\"y\":690,\"rotation\":0},{\"id\":\"item_1731700019012\",\"type\":\"fogao\",\"x\":290,\"y\":690,\"rotation\":0},{\"id\":\"item_1731700019013\",\"type\":\"fogao\",\"x\":420,\"y\":690,\"rotation\":0},{\"id\":\"item_1731700019014\",\"type\":\"fogao\",\"x\":550,\"y\":690,\"rotation\":0},{\"id\":\"item_1731700019015\",\"type\":\"fritadeira\",\"x\":680,\"y\":690,\"rotation\":0},{\"id\":\"item_1731700019016\",\"type\":\"fritadeira\",\"x\":770,\"y\":690,\"rotation\":0},{\"id\":\"item_1731700019017\",\"type\":\"fritadeira\",\"x\":860,\"y\":690,\"rotation\":0},{\"id\":\"item_1731700019019\",\"type\":\"pia\",\"x\":100,\"y\":280,\"rotation\":0},{\"id\":\"item_1731700019020\",\"type\":\"pia\",\"x\":240,\"y\":280,\"rotation\":0},{\"id\":\"item_1731700019021\",\"type\":\"bancada\",\"x\":380,\"y\":280,\"rotation\":0},{\"id\":\"item_1731700019022\",\"type\":\"bancada\",\"x\":500,\"y\":280,\"rotation\":0},{\"id\":\"item_1731700019023\",\"type\":\"balcao\",\"x\":620,\"y\":280,\"rotation\":0},{\"id\":\"item_1731700019024\",\"type\":\"balcao\",\"x\":730,\"y\":280,\"rotation\":0},{\"id\":\"item_1731700019025\",\"type\":\"talheres\",\"x\":840,\"y\":295,\"rotation\":0},{\"id\":\"item_1731700019027\",\"type\":\"panelas\",\"x\":1010,\"y\":290,\"rotation\":0},{\"id\":\"item_1731700019028\",\"type\":\"pia\",\"x\":120,\"y\":440,\"rotation\":0},{\"id\":\"item_1731700019029\",\"type\":\"bancada\",\"x\":260,\"y\":440,\"rotation\":0},{\"id\":\"item_1731700019030\",\"type\":\"bancada\",\"x\":380,\"y\":440,\"rotation\":0},{\"id\":\"item_1731700019031\",\"type\":\"bancada\",\"x\":500,\"y\":440,\"rotation\":0},{\"id\":\"item_1731700019032\",\"type\":\"balcao\",\"x\":620,\"y\":440,\"rotation\":0},{\"id\":\"item_1731700019033\",\"type\":\"balcao\",\"x\":730,\"y\":440,\"rotation\":0},{\"id\":\"item_1731700019034\",\"type\":\"talheres\",\"x\":840,\"y\":455,\"rotation\":0},{\"id\":\"item_1731700019036\",\"type\":\"panelas\",\"x\":1000,\"y\":450,\"rotation\":0},{\"id\":\"item_1731700019037\",\"type\":\"porta\",\"x\":20,\"y\":775,\"rotation\":0},{\"id\":\"item_1731700019038\",\"type\":\"porta\",\"x\":1090,\"y\":775,\"rotation\":0},{\"id\":\"item_1731700019039\",\"type\":\"porta\",\"x\":1090,\"y\":0,\"rotation\":0},{\"id\":\"item_1731700019040\",\"type\":\"janela\",\"x\":240,\"y\":0,\"rotation\":0},{\"id\":\"item_1731700019041\",\"type\":\"janela\",\"x\":500,\"y\":0,\"rotation\":0},{\"id\":\"item_1731700019042\",\"type\":\"janela\",\"x\":760,\"y\":0,\"rotation\":0}]', '2026-11-15 11:07:40');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_mesas`
--

CREATE TABLE `tb_mesas` (
  `id_mesa` int(11) NOT NULL,
  `id_gestor` int(11) NOT NULL,
  `numero_mesa` int(11) NOT NULL,
  `status` enum('ativa','inativa') NOT NULL DEFAULT 'inativa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_mesas`
--

INSERT INTO `tb_mesas` (`id_mesa`, `id_gestor`, `numero_mesa`, `status`) VALUES
(1, 5, 1, 'ativa'),
(2, 5, 2, 'ativa'),
(3, 5, 3, 'inativa'),
(4, 5, 4, 'inativa'),
(5, 5, 5, 'ativa'),
(6, 5, 6, 'inativa'),
(7, 5, 7, 'ativa'),
(8, 5, 8, 'inativa'),
(9, 5, 9, 'ativa'),
(10, 5, 10, 'inativa'),
(11, 5, 11, 'inativa'),
(12, 5, 12, 'inativa'),
(13, 5, 13, 'inativa'),
(14, 5, 14, 'inativa'),
(15, 5, 15, 'inativa'),
(16, 5, 16, 'inativa'),
(17, 5, 17, 'inativa'),
(18, 5, 18, 'inativa'),
(19, 5, 19, 'inativa'),
(20, 5, 20, 'inativa');

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_pagamentos`
--

CREATE TABLE `tb_pagamentos` (
  `id_pagamento` int(11) NOT NULL,
  `id_gestor` int(11) NOT NULL,
  `meses` int(11) NOT NULL,
  `valor` decimal(10,2) NOT NULL,
  `status` enum('aguardando','confirmado','cancelado') NOT NULL DEFAULT 'aguardando',
  `data_solicitacao` datetime NOT NULL DEFAULT current_timestamp(),
  `data_confirmacao` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_pagamentos`
--

INSERT INTO `tb_pagamentos` (`id_pagamento`, `id_gestor`, `meses`, `valor`, `status`, `data_solicitacao`, `data_confirmacao`) VALUES
(1, 5, 1, 10.00, 'cancelado', '2026-09-23 17:47:44', NULL),
(2, 5, 12, 120.00, 'confirmado', '2026-10-28 20:34:16', '2026-11-07 20:28:18'),
(3, 5, 1, 10.00, 'cancelado', '2026-11-16 09:28:53', NULL),
(4, 5, 6, 60.00, 'cancelado', '2026-11-16 14:27:17', NULL),
(5, 5, 3, 30.00, 'cancelado', '2026-11-17 14:01:43', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_pedidos`
--

CREATE TABLE `tb_pedidos` (
  `id_pedido` int(11) NOT NULL,
  `id_gestor` int(11) NOT NULL,
  `id_mesa` int(11) DEFAULT NULL,
  `nome_cliente` varchar(150) DEFAULT NULL,
  `cpf_cliente` varchar(14) DEFAULT NULL,
  `telefone_cliente` varchar(20) DEFAULT NULL,
  `data_pedido` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `valor_total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('aberto','fechado') NOT NULL DEFAULT 'aberto',
  `data_abertura` datetime DEFAULT NULL,
  `data_fechamento` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_pedidos`
--

INSERT INTO `tb_pedidos` (`id_pedido`, `id_gestor`, `id_mesa`, `nome_cliente`, `cpf_cliente`, `telefone_cliente`, `data_pedido`, `valor_total`, `status`, `data_abertura`, `data_fechamento`) VALUES
(1, 5, 1, 'Nicole', '12345612378', '(14) 99807-7182', '2026-09-23 21:24:33', 35.10, 'fechado', '2026-09-23 18:23:44', '2026-09-23 18:24:33'),
(2, 5, 8, 'Mariana Souza', NULL, '(14) 99914-2408', '2026-10-12 15:43:00', 61.15, 'fechado', '2026-10-12 11:42:00', '2026-10-12 12:43:00'),
(3, 5, 9, 'Marcos Vinícius', NULL, '(14) 99492-4640', '2026-10-13 21:39:00', 25.50, 'fechado', '2026-10-13 18:04:00', '2026-10-13 18:39:00'),
(4, 5, 9, 'Gabriel Teixeira', NULL, '(14) 99419-9630', '2026-10-14 17:25:00', 54.89, 'fechado', '2026-10-14 13:02:00', '2026-10-14 14:25:00'),
(5, 5, 6, 'Paulo Ricardo', NULL, '(14) 98980-7115', '2026-10-16 01:39:00', 219.60, 'fechado', '2026-10-15 21:33:00', '2026-10-15 22:39:00'),
(6, 5, 5, 'Juliana Barros', NULL, '(14) 98265-4330', '2026-10-16 23:40:00', 112.64, 'fechado', '2026-10-16 20:10:00', '2026-10-16 20:40:00'),
(7, 5, 8, 'Carlos Henrique', NULL, '(14) 98395-3755', '2026-10-17 15:39:00', 22.99, 'fechado', '2026-10-17 11:51:00', '2026-10-17 12:39:00'),
(8, 5, 7, 'Roberto Lima', NULL, '(14) 99846-4713', '2026-10-18 00:53:00', 84.84, 'fechado', '2026-10-17 21:05:00', '2026-10-17 21:53:00'),
(9, 5, 11, 'André Luiz', NULL, '(14) 98174-7698', '2026-10-18 00:42:00', 144.49, 'fechado', '2026-10-17 21:10:00', '2026-10-17 21:42:00'),
(10, 5, 8, 'Simone Duarte', NULL, '(14) 98064-9676', '2026-10-18 15:45:00', 110.45, 'fechado', '2026-10-18 11:58:00', '2026-10-18 12:45:00'),
(11, 5, 1, 'Diego Fernandes', NULL, '(14) 99244-7883', '2026-10-18 16:39:00', 79.39, 'fechado', '2026-10-18 12:59:00', '2026-10-18 13:39:00'),
(12, 5, 1, 'Lucas Batista', NULL, '(14) 99782-1678', '2026-10-19 01:50:00', 113.70, 'fechado', '2026-10-18 21:52:00', '2026-10-18 22:50:00'),
(13, 5, 7, 'Luciana Costa', NULL, '(14) 98534-6794', '2026-10-19 23:29:00', 96.37, 'fechado', '2026-10-19 19:32:00', '2026-10-19 20:29:00'),
(14, 5, 9, 'Cláudia Mendes', NULL, '(14) 98159-3538', '2026-10-22 01:37:00', 95.38, 'fechado', '2026-10-21 21:35:00', '2026-10-21 22:37:00'),
(15, 5, 1, 'Fernanda Alves', NULL, '(14) 99799-8608', '2026-10-21 16:12:00', 61.14, 'fechado', '2026-10-21 11:57:00', '2026-10-21 13:12:00'),
(16, 5, 4, 'Camila Ribeiro', NULL, '(14) 98547-2306', '2026-10-21 22:21:00', 89.79, 'fechado', '2026-10-21 18:19:00', '2026-10-21 19:21:00'),
(17, 5, 7, 'João Pedro', NULL, '(14) 98521-1200', '2026-10-22 22:44:00', 91.93, 'fechado', '2026-10-22 18:42:00', '2026-10-22 19:44:00'),
(18, 5, 2, 'Daniela Freitas', NULL, '(14) 98438-8473', '2026-10-22 17:07:00', 122.60, 'fechado', '2026-10-22 13:17:00', '2026-10-22 14:07:00'),
(19, 5, 1, 'Beatriz Rocha', NULL, '(14) 99581-6938', '2026-10-23 23:04:00', 67.13, 'fechado', '2026-10-23 19:03:00', '2026-10-23 20:04:00'),
(20, 5, 12, 'Patrícia Gomes', NULL, '(14) 99146-2114', '2026-10-23 23:38:00', 57.97, 'fechado', '2026-10-23 19:33:00', '2026-10-23 20:38:00'),
(21, 5, 7, 'Eduardo Nunes', NULL, '(14) 99614-1761', '2026-10-24 16:12:00', 104.89, 'fechado', '2026-10-24 12:18:00', '2026-10-24 13:12:00'),
(22, 5, 9, 'Felipe Araújo', NULL, '(14) 99897-6317', '2026-10-24 21:51:00', 167.53, 'fechado', '2026-10-24 18:00:00', '2026-10-24 18:51:00'),
(23, 5, 7, 'Bruno Carvalho', NULL, '(14) 98029-3463', '2026-10-25 22:06:00', 26.00, 'fechado', '2026-10-25 18:26:00', '2026-10-25 19:06:00'),
(24, 5, 6, 'Henrique Silva', NULL, '(14) 98208-7282', '2026-10-27 17:26:00', 114.30, 'fechado', '2026-10-27 13:47:00', '2026-10-27 14:26:00'),
(25, 5, 3, 'Rafael Dias', NULL, '(14) 98797-4760', '2026-10-27 15:50:00', 62.10, 'fechado', '2026-10-27 11:47:00', '2026-10-27 12:50:00'),
(26, 5, 3, 'Ana Paula', NULL, '(14) 99129-9100', '2026-10-31 23:43:00', 36.49, 'fechado', '2026-10-31 20:05:00', '2026-10-31 20:43:00'),
(27, 5, 2, 'Thiago Martins', NULL, '(14) 99172-5706', '2026-10-31 16:12:00', 44.14, 'fechado', '2026-10-31 12:16:00', '2026-10-31 13:12:00'),
(28, 5, 9, 'Michele Santos', NULL, '(14) 99090-2644', '2026-10-31 23:44:00', 68.96, 'fechado', '2026-10-31 19:42:00', '2026-10-31 20:44:00'),
(29, 5, 1, 'Renata Farias', NULL, '(14) 99583-4416', '2026-11-01 23:58:00', 30.15, 'fechado', '2026-11-01 20:25:00', '2026-11-01 20:58:00'),
(30, 5, 8, 'Aline Cardoso', NULL, '(14) 99953-2199', '2026-11-01 22:28:00', 127.89, 'fechado', '2026-11-01 18:29:00', '2026-11-01 19:28:00'),
(31, 5, 11, 'Vinícius Prado', NULL, '(14) 99007-4612', '2026-11-02 16:52:00', 101.40, 'fechado', '2026-11-02 12:35:00', '2026-11-02 13:52:00'),
(32, 5, 7, 'Tatiane Melo', NULL, '(14) 99544-1208', '2026-11-03 17:46:00', 90.69, 'fechado', '2026-11-03 13:44:00', '2026-11-03 14:46:00'),
(33, 5, 3, 'Isabela Pires', NULL, '(14) 99165-7199', '2026-11-03 15:51:00', 85.00, 'fechado', '2026-11-03 11:52:00', '2026-11-03 12:51:00'),
(34, 5, 1, 'Vanessa Lopes', NULL, '(14) 98485-9485', '2026-11-04 16:25:00', 72.90, 'fechado', '2026-11-04 11:51:00', '2026-11-04 13:25:00'),
(35, 5, 7, 'Larissa Moura', NULL, '(14) 98995-5898', '2026-11-05 15:32:00', 27.99, 'fechado', '2026-11-05 11:55:00', '2026-11-05 12:32:00'),
(36, 5, 9, 'Leonardo Cunha', NULL, '(14) 99920-8910', '2026-11-05 15:41:00', 34.99, 'fechado', '2026-11-05 12:12:00', '2026-11-05 12:41:00'),
(37, 5, 4, 'Mariana Souza', NULL, '(14) 99063-9071', '2026-11-05 23:27:00', 34.99, 'fechado', '2026-11-05 19:24:00', '2026-11-05 20:27:00'),
(38, 5, 10, 'Marcos Vinícius', NULL, '(14) 99746-3036', '2026-11-07 00:36:00', 112.70, 'fechado', '2026-11-06 20:40:00', '2026-11-06 21:36:00'),
(39, 5, 4, 'Gabriel Teixeira', NULL, '(14) 98197-9685', '2026-11-08 01:28:00', 86.72, 'fechado', '2026-11-07 21:45:00', '2026-11-07 22:28:00'),
(40, 5, 1, 'Paulo Ricardo', NULL, '(14) 99644-7176', '2026-11-07 17:22:00', 65.25, 'fechado', '2026-11-07 13:33:00', '2026-11-07 14:22:00'),
(41, 5, 12, 'Juliana Barros', NULL, '(14) 98192-6041', '2026-11-08 15:49:00', 159.09, 'fechado', '2026-11-08 11:46:00', '2026-11-08 12:49:00'),
(42, 5, 7, 'Carlos Henrique', NULL, '(14) 98472-8369', '2026-11-08 22:54:00', 28.89, 'fechado', '2026-11-08 19:11:00', '2026-11-08 19:54:00'),
(43, 5, 6, 'Roberto Lima', NULL, '(14) 99354-5966', '2026-11-08 15:50:00', 157.47, 'fechado', '2026-11-08 11:50:00', '2026-11-08 12:50:00'),
(44, 5, 3, 'André Luiz', NULL, '(14) 99174-4089', '2026-11-10 16:53:00', 78.68, 'fechado', '2026-11-10 12:55:00', '2026-11-10 13:53:00'),
(45, 5, 12, 'Simone Duarte', NULL, '(14) 98667-6710', '2026-11-12 01:19:00', 53.89, 'fechado', '2026-11-11 21:35:00', '2026-11-11 22:19:00'),
(46, 5, 9, 'Diego Fernandes', NULL, '(14) 98173-5014', '2026-11-13 23:59:00', 56.59, 'fechado', '2026-11-13 20:19:00', '2026-11-13 20:59:00'),
(47, 5, 2, 'Lucas Batista', NULL, '(14) 99658-6569', '2026-11-13 15:47:00', 44.14, 'fechado', '2026-11-13 11:47:00', '2026-11-13 12:47:00'),
(48, 5, 5, 'Luciana Costa', NULL, '(14) 98063-4329', '2026-11-14 01:59:36', 75.84, 'aberto', '2026-11-13 22:24:36', NULL),
(49, 5, 7, 'Cláudia Mendes', NULL, '(14) 98031-1027', '2026-11-14 01:59:36', 55.98, 'aberto', '2026-11-13 22:09:36', NULL),
(50, 5, 9, 'Fernanda Alves', NULL, '(14) 98786-7237', '2026-11-14 01:59:36', 57.98, 'aberto', '2026-11-13 22:41:36', NULL),
(51, 5, 2, 'Camila Ribeiro', NULL, '(14) 98342-2345', '2026-11-14 01:59:36', 33.40, 'aberto', '2026-11-13 22:51:36', NULL),
(52, 5, 1, 'jihyiy8', NULL, '(41) 44444-4444', '2026-11-16 12:27:41', 0.00, 'aberto', '2026-11-16 09:27:41', NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_pratos`
--

CREATE TABLE `tb_pratos` (
  `id_prato` int(11) NOT NULL,
  `id_gestor` int(11) NOT NULL,
  `nome_prato` text NOT NULL,
  `imagem` varchar(255) DEFAULT NULL,
  `preco_venda` decimal(10,2) NOT NULL,
  `modo_preparo` text DEFAULT NULL,
  `ativo` tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Despejando dados para a tabela `tb_pratos`
--

INSERT INTO `tb_pratos` (`id_prato`, `id_gestor`, `nome_prato`, `imagem`, `preco_venda`, `modo_preparo`, `ativo`) VALUES
(1, 1, 'Risoto de alho poro', NULL, 50.00, NULL, 1),
(2, 1, 'guarana', NULL, 20.00, NULL, 1),
(3, 5, 'strogonoff', 'prato_6ab1618a4c0b5_1790009738.jpg', 35.10, '--> Strogonoff\r\n\r\n1\r\nEm uma panela coloque o óleo, a cebola picada e o alho para dourar.\r\n\r\n2\r\nEm seguida, coloque a carne cortada em cubinhos para fritar.\r\n\r\n3\r\nAbaixe o fogo e deixe cozinhar por alguns minutos, até a carne ficar molinha. Se necessário, vá acrescentando água aos poucos até atingir o cozimento desejado.\r\n\r\n4\r\nAcrescente a mostarda e o ketchup e misture bem.\r\n\r\n5\r\nTempere com sal e pimenta a gosto.\r\n\r\n6\r\nAcrescente a cebolinha e a salsinha picadas e misture.\r\n\r\n7\r\nDesligue o fogo e acrescente o creme de leite.\r\n\r\n--> Arroz\r\n\r\nRefogue o alho e a cebola no azeite.\r\n\r\n2\r\nColoque o arroz e deixe fritar por cerca de 30 segundos.\r\n\r\n3\r\nAdicione a água fervente e o sal.\r\n\r\n4\r\nAbaixe o fogo e deixe cozinhar até a água quase secar\r\n\r\n5\r\nTampe a panela e aguarde cerca de 20 minutos antes de servir.\r\n\r\n6\r\nAdicione tudo a um prato e sirva', 1),
(4, 5, 'Bifé acebolado', 'prato_6ab161e18d666_1790009825.jpg', 39.90, '--> Salada de Alface e Tomate\r\n1\r\nLave bem as folhas de alface e os tomates em água corrente, deixando de molho em solução sanitizante (ou água com hipoclorito) por 15 minutos, e depois enxágue.\r\n\r\n2\r\nRasgue ou fatie as folhas de alface e corte os tomates em rodelas ou meias-luas.\r\n\r\n3\r\nDeixe montado no prato ou tigela e tempere com azeite, sal e vinagre (ou limão) apenas na hora de servir para a alface não murchar.\r\n\r\n--> Arroz\r\n1\r\nEm uma panela média, aqueça 1 fio de óleo ou azeite e refogue 2 dentes de alho amassados (e cebola picada, se preferir) sem deixar queimar.\r\n\r\n2\r\nAdicione 1 xícara de arroz lavado e escorrido, refogue por 1 minuto, tempere com sal e acrescente 2 xícaras de água quente.\r\n\r\n3\r\nCozinhe em fogo médio com a panela semi-tampada até a água secar; depois abaixe o fogo, tampe totalmente por mais 3 minutos, desligue e deixe descansar fechado por 5 minutos.\r\n\r\n--> Batata Frita\r\n1\r\nDescasque e corte as batatas em palitos uniformes; lave em água fria para tirar o excesso de amido e seque muito bem com um pano de prato limpo ou papel-toalha.\r\n\r\n2\r\nAqueça óleo suficiente em uma panela funda até ficar bem quente (cerca de 180°C) e frite as batatas aos poucos para não esfriar o óleo.\r\n\r\n3\r\nRetire quando estiverem douradas e crocantes, coloque sobre papel-toalha para absorver o excesso de óleo e tempere com sal imediatamente. (Dica: Se usar AirFryer, asse a 200°C por cerca de 20 a 25 minutos, chacoalhando na metade do tempo).\r\n\r\n--> Bife Acebolado\r\n1\r\nTempere os bifes (contrafilé, alcatra ou patinho) com sal, pimenta-do-reino e alho amassado momentos antes de fritar.\r\n\r\n2\r\nSelar a carne: Aqueça bem uma frigideira larga com 1 fio de óleo ou azeite, coloque os bifes sem sobrepor e deixe dourar por cerca de 2 minutos de cada lado (sem ficar mexendo para não juntar água). Retire os bifes e reserve em um prato coberto para manter o calor.\r\n\r\n3\r\nNa mesma frigideira, aproveitando o fundo de sabor da carne, coloque 1 cebola grande cortada em rodelas, adicione uma pitada de sal e refogue até murchar e dourar levemente (se quiser, pingue 1 colher de sopa de água ou molho shoyu para soltar o caldinho). Volte os bifes para a frigideira por alguns segundos só para envolver na cebola e sirva em seguida.', 1),
(6, 5, 'Porção Ovo de Codorno', 'prato_6aefb72444e97_1794094884.jpg', 15.00, '1 Pegar uma panela e encher ate a metade de agua\r\n\r\n2\r\nColonar no fogão e deixar em fogo alto por 5 minutos\r\n\r\n3\r\nAdicionar 15 ovos de codorna na panela e esperar por 15 minutos\r\n\r\n4\r\nRetirar os ovos e descasca-los\r\n\r\n5\r\nAdicionar a um recipiente, colocar sal e cheiro verde, depois só servir', 1),
(7, 5, 'Salada', 'prato_6aefb742cb5a7_1794094914.png', 18.50, '1 \r\nPicar tomate\r\n\r\n2\r\nPicar pepino\r\n\r\n3\r\nPicar cebola\r\n\r\n4\r\nPicar cenoura\r\n\r\n5\r\nAdicionar cebola, pepino, tomate, cenoura e alface em um recipiente\r\n\r\n6\r\nTemperar com sazon e vinagre', 1),
(8, 5, 'Batata Frita', 'prato_6aefb758957c4_1794094936.jpg', 26.70, '1\r\nRetirar as batatas prontas do plástico\r\n\r\n2\r\nAqueça óleo suficiente em uma panela funda até ficar bem quente (cerca de 180°C) e frite as batatas aos poucos para não esfriar o óleo.\r\n\r\n3\r\nRetire quando estiverem douradas e crocantes, coloque sobre papel-toalha para absorver o excesso de óleo e tempere com sal imediatamente.', 1),
(9, 5, 'Espetinho de Medalhão', 'prato_6aefb77290e33_1794094962.jpg', 12.00, '1\r\nEm uma tigela, misture os cubos de peito de frango com o alho, sal (com moderação por causa do bacon), pimenta, páprica e um fio de azeite ou suco de meio limão para manter a suculência. Deixe marinar por pelo menos 20 a 30 minutos na geladeira para pegar bem o tempero.\r\n2\r\nPegue um cubo de frango temperado e envolva-o completamente nas laterais com uma fatia fina de bacon.\r\n3\r\nAtravesse o palito bem no meio do medalhão, passando pelas pontas do bacon para prendê-lo bem e evitar que se solte ao assar. Coloque de 3 a 4 medalhões por espeto, deixando um pequeno espaço entre eles para o calor circular e dourar o frango por igual.\r\n4\r\nLeve ao fogo ou forno até o bacon ficar bem dourado e crocante e o frango totalmente cozido por dentro, tomando cuidado para não ressecar.\r\n\r\n5\r\nRetirar e servir em um recipiente com 1 alface por baixo', 1),
(10, 5, 'Espetinho de Filé Minhon', 'prato_6aefb79d1dbe6_1794095005.jpg', 15.00, '1\r\nLimpe o filé mignon retirando o excesso de gordura ou nervos e corte a carne em cubos uniformes de aproximadamente 3 a 4 cm para que assem por igual.\r\n\r\n2\r\nEm uma tigela, regue os cubos de carne com o azeite e misture a pimenta-do-reino e o alho (deixe para colocar o sal apenas momentos antes de levar ao fogo, para não desidratar e ressecar a carne).\r\n\r\n3\r\nEspete de 4 a 5 cubos de filé mignon em cada palito, deixando um pequeno espaço entre eles (se desejar, intercale com pedaços de cebola e pimentão para dar mais sabor e cor).\r\n\r\n4\r\nSalpique o sal de parrilla ou sal grosso em todos os lados dos espetinhos logo antes de assar.\r\n\r\n5\r\nLeve à churrasqueira em brasa forte (ou frigideira/chapa bem quente) por cerca de 8 a 12 minutos no total, virando todos os lados para selar por fora e manter o interior macio e suculento.\r\n\r\n6\r\nSirva em um recipiente', 1),
(11, 5, 'Pão de Alho', 'prato_6aefb7c762314_1794095047.webp', 10.60, '1\r\nLigue o forno em temperatura média (180°C a 200°C) por cerca de 10 minutos antes de colocar os pães.\r\n\r\n2\r\nRetire os 4 pães da embalagem plástica e coloque-os em uma assadeira (não precisa untar).\r\n\r\n3\r\nLeve ao forno por 8 a 12 minutos se o pão estiver resfriado (da geladeira), ou 12 a 15 minutos se estiver congelado.\r\n\r\n4\r\nRetire e sirva em um recipiente', 1),
(12, 5, 'Suco de laranja', 'prato_6aefb9154dd65_1794095381.webp', 13.50, 'Retirar da geladeira e servir', 1),
(13, 5, 'Suco de Laranja - Copo', 'prato_6aefb940f08b4_1794095424.png', 14.20, '1\r\nCortar as laranjas ao meio\r\n\r\n2\r\nEspremer em um copo\r\n\r\n3\r\nAdicionar gelo e servir', 1),
(14, 5, 'Amburgue - Cheedar', 'prato_6aefb9752c797_1794095477.webp', 30.15, '1\r\nCorte os pães ao meio, passe uma camada fina de manteiga no miolo e doure rapidamente em uma frigideira quente até formar uma casquinha crocante. Reserve.\r\n\r\n2\r\nMolde as porções de carne em discos um pouco maiores que o diâmetro do pão (pois a carne encolhe ao assar) e faça uma leve pressão no centro com o dedo para que ele não estufe.\r\n\r\n3\r\nAqueça muito bem uma frigideira de ferro ou chapa com um fio de óleo. Tempere um lado da carne com sal e pimenta, coloque-a na chapa com o lado temperado para baixo e tempere o outro lado. Deixe grelhar por cerca de 2 minutos e meio sem mexer até formar uma crosta dourada.\r\n\r\n4\r\nVire o hambúrguer, coloque 2 fatias de queijo cheddar sobre cada disco de carne, pingue 1 colher de chá de água na chapa e tampe a frigideira por cerca de 1 minuto e meio para o vapor derreter o queijo perfeitamente.\r\n\r\n5\r\nMontar igual ao da foto: Passe uma camada fina de maionese na base do pão, acomode o hambúrguer com o cheddar derretido por cima e adicione as rodelas de picles. Espalhe a cebola picadinha sobre o picles, finalize com fios de ketchup e mostarda na parte superior e feche o lanche.', 1),
(15, 5, 'Amburgue - Bacon', 'prato_6aefb994c48bc_1794095508.jpg', 22.99, '1\r\nEm uma frigideira fria (sem óleo), disponha as fatias de bacon lado a lado e leve ao fogo baixo. Deixe fritar lentamente, virando na metade do tempo, até ficarem bem douradas e crocantes. Retire e coloque sobre papel-toalha para escorrer a gordura.\r\n\r\n2\r\nCorte os pães ao meio, passe uma camada fina de manteiga no miolo e doure rapidamente na frigideira quente até formar uma casquinha crocante (isso evita que o pão encharque com o suco da carne e do tomate). Reserve.\r\n\r\n3\r\nMolde as porções de carne em discos altos (cerca de 2 cm de altura) e faça uma leve pressão no centro. Aqueça muito bem uma frigideira de ferro ou chapa com um fio de óleo, tempere um lado da carne com sal e pimenta, coloque na chapa com o lado temperado para baixo e tempere o outro lado. Grelhe por cerca de 3 minutos sem mexer até formar uma crosta dourada.\r\n\r\n4\r\nVire o hambúrguer, coloque 1 fatia de cheddar sobre a carne, acomode as fatias de bacon crocante por cima e cubra com a segunda fatia de cheddar (igual à montagem da foto, onde o queijo abraça o bacon). Pingue 1 colher de chá de água na chapa e tampe por cerca de 1 minuto e meio até o queijo derreter por completo.\r\n\r\n5\r\nPasse maionese na base do pão, acomode as folhas de alface e duas rodelas de tomate por cima. Coloque o hambúrguer com o cheddar e o bacon sobre o tomate, feche com a tampa do pão brioche e sirva imediatamente.', 1),
(16, 5, 'Refeição - Filé de Frango Empanado', 'prato_6aefb9c4c4acb_1794095556.png', 22.99, '--> Arroz\r\n1\r\nRefogue o alho e a cebola no azeite.\r\n\r\n2\r\nColoque o arroz e deixe fritar por cerca de 30 segundos.\r\n\r\n3\r\nAdicione a água fervente e o sal.\r\n\r\n4\r\nAbaixe o fogo e deixe cozinhar até a água quase secar\r\n\r\n5\r\nTampe a panela e aguarde cerca de 20 minutos antes de servir.\r\n\r\n--> Feijão\r\n1\r\nDeixe o feijão de molho por 2 horas.\r\n\r\n2\r\nEscorra e transfira para uma panela de pressão.\r\n\r\n3\r\nJunte a água, o tempero pronto, o sal e o louro.\r\n\r\n4\r\nDeixe cozinhar, em fogo baixo, por 20 minutos após o início da fervura.\r\n\r\n5\r\nEm uma frigideira média, coloque o óleo e leve ao fogo alto para aquecer. Junte o alho e refogue rapidamente até dourar.\r\n\r\n6\r\nAdicione uma concha dos grãos do feijão cozido e amasse-os com uma colher.\r\n\r\n7\r\nVolte o refogado à panela de pressão e deixe cozinhar, com a panela semi-tampada, por 10 minutos, ou até encorpar ligeiramente.\r\n\r\n8\r\nRetire do fogo e sirva em seguida.\r\n\r\n--> Salada\r\n1\r\nLave bem o alface.\r\n\r\n2\r\nPique as folhas e o tomate. Misture tudo.\r\n\r\n3\r\nAdicione a um recipiente.\r\n\r\n--> Batata Frita\r\n1\r\nCorte as batas em palito e seque-as com papel toalha.\r\n\r\n2\r\nColoque o óleo para esquentar, quando estiver bem quente, retire 1/2 xícara desse óleo e misture a 1/2 colher de amido de milho.\r\n\r\n3\r\nApós misturado, volte esse óleo para frigideira, frite as batatas em fogo médio até dourarem.', 1),
(17, 5, 'Guaraná Antartica - 2L', 'prato_6aefb9ee76679_1794095598.jpg', 8.99, 'Retirar da geladeira e servir', 1),
(18, 5, 'Fanta Laranja - Lata', 'prato_6aefba08b79d1_1794095624.webp', 5.00, 'Retirar da geladeira e servir', 1),
(19, 5, 'Coca Cola - 2L', 'prato_6aefba34ea09c_1794095668.webp', 13.99, 'Retirar da geladeira e servir', 1),
(20, 5, 'Coca Cola - 1L', 'prato_6aefba516012f_1794095697.png', 8.00, 'Retirar da geladeira e servir', 1),
(21, 5, 'Coca Cola - 600ml', 'prato_6aefba76525db_1794095734.webp', 6.00, 'Retirar da geladeira e servir', 1),
(22, 5, 'Coca Cola - Lata', 'prato_6aefba93d5abc_1794095763.jpg', 5.00, 'Retirar da geladeira e servir', 1),
(23, 5, 'Parmegiana de Frango', 'prato_6aefbb4980aac_1794095945.png', 26.00, '1\r\nFrite os empanados e reserve.\r\n\r\n2\r\nEm uma assadeira colocar os empados fritos e despejar extrato de tomate\r\n\r\n3\r\nColoque no forno por 20 minutos\r\n\r\n4\r\nAdicione uma fatia de queijo por cima e espere 3 minutos.\r\n\r\n5\r\nRetire o empanado, coloque em uma travessa.\r\n\r\n--> Arroz\r\n1\r\nRefogue o alho e a cebola no azeite.\r\n\r\n2\r\nColoque o arroz e deixe fritar por cerca de 30 segundos.\r\n\r\n3\r\nAdicione a água fervente e o sal.\r\n\r\n4\r\nAbaixe o fogo e deixe cozinhar até a água quase secar\r\n\r\n5\r\nTampe a panela e aguarde cerca de 20 minutos antes de servir.\r\n\r\n6\r\nAdicione tudo a um prato e sirva', 1),
(24, 5, 'Macarronada', 'prato_6aefbba7e041c_1794096039.jpg', 19.90, '1\r\nColoque o macarrão em uma panela com água e cozinhe por 10 minutos.\r\n\r\n2\r\nColoque em outra panela 1 colher de azeite na panela em fogo alto e a cebola picada.\r\n\r\n3\r\nQuando a cebola dourar, acrescente os tomates e mexa até que eles soltem água, depois disso, acrescente 1 colher de extrato de tomate e a pimenta-do-reino moída.\r\n\r\n4\r\nMexa até misturar tudo e reserve.\r\n\r\n5\r\nEscorra o macarrão, coloque-o em um refratário, acrescente o molho, misture e sirva em um prato.', 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `tb_recuperacao_senha`
--

CREATE TABLE `tb_recuperacao_senha` (
  `id_recuperacao` int(11) NOT NULL,
  `id_gestor` int(11) NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expira_em` datetime NOT NULL,
  `usado` tinyint(1) NOT NULL DEFAULT 0,
  `criado_em` datetime NOT NULL DEFAULT current_timestamp(),
  `ip` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `tb_desperdicio`
--
ALTER TABLE `tb_desperdicio`
  ADD PRIMARY KEY (`id_desperdicio`),
  ADD KEY `fk_desperdicio_ingrediente` (`id_ingrediente`);

--
-- Índices de tabela `tb_estoque`
--
ALTER TABLE `tb_estoque`
  ADD PRIMARY KEY (`id_ingrediente`),
  ADD KEY `fk_estoque_gestor` (`id_gestor`);

--
-- Índices de tabela `tb_fornecedores`
--
ALTER TABLE `tb_fornecedores`
  ADD PRIMARY KEY (`id_fornecedor`),
  ADD KEY `fk_fornecedores_gestor` (`id_gestor`);

--
-- Índices de tabela `tb_fornecedor_estoque`
--
ALTER TABLE `tb_fornecedor_estoque`
  ADD PRIMARY KEY (`id_fornecedor`,`id_ingrediente`),
  ADD KEY `idx_fe_ingrediente` (`id_ingrediente`);

--
-- Índices de tabela `tb_funcionarios`
--
ALTER TABLE `tb_funcionarios`
  ADD PRIMARY KEY (`id_funcionario`),
  ADD KEY `fk_funcionarios_gestor` (`id_gestor`);

--
-- Índices de tabela `tb_gestor`
--
ALTER TABLE `tb_gestor`
  ADD PRIMARY KEY (`id_gestor`);

--
-- Índices de tabela `tb_item_ficha_tecnica`
--
ALTER TABLE `tb_item_ficha_tecnica`
  ADD PRIMARY KEY (`id_item_ficha`),
  ADD KEY `fk_ficha_prato` (`id_prato`),
  ADD KEY `fk_ficha_ingrediente` (`id_ingrediente`);

--
-- Índices de tabela `tb_itens_pedido`
--
ALTER TABLE `tb_itens_pedido`
  ADD PRIMARY KEY (`id_itens`),
  ADD KEY `fk_itens_pedido_pedido` (`id_pedido`),
  ADD KEY `fk_itens_pedido_prato` (`id_prato`);

--
-- Índices de tabela `tb_kanban_tarefas`
--
ALTER TABLE `tb_kanban_tarefas`
  ADD PRIMARY KEY (`id_tarefa`),
  ADD KEY `idx_kanban_gestor_status` (`id_gestor`,`status`);

--
-- Índices de tabela `tb_layout`
--
ALTER TABLE `tb_layout`
  ADD PRIMARY KEY (`id_layout`),
  ADD KEY `fk_layout_gestor` (`id_gestor`);

--
-- Índices de tabela `tb_mesas`
--
ALTER TABLE `tb_mesas`
  ADD PRIMARY KEY (`id_mesa`),
  ADD UNIQUE KEY `uq_gestor_mesa` (`id_gestor`,`numero_mesa`);

--
-- Índices de tabela `tb_pagamentos`
--
ALTER TABLE `tb_pagamentos`
  ADD PRIMARY KEY (`id_pagamento`),
  ADD KEY `idx_pagamentos_gestor` (`id_gestor`);

--
-- Índices de tabela `tb_pedidos`
--
ALTER TABLE `tb_pedidos`
  ADD PRIMARY KEY (`id_pedido`),
  ADD KEY `fk_pedidos_gestor` (`id_gestor`);

--
-- Índices de tabela `tb_pratos`
--
ALTER TABLE `tb_pratos`
  ADD PRIMARY KEY (`id_prato`),
  ADD KEY `fk_pratos_gestor` (`id_gestor`);

--
-- Índices de tabela `tb_recuperacao_senha`
--
ALTER TABLE `tb_recuperacao_senha`
  ADD PRIMARY KEY (`id_recuperacao`),
  ADD UNIQUE KEY `uk_token_hash` (`token_hash`),
  ADD KEY `idx_gestor_criado` (`id_gestor`,`criado_em`),
  ADD KEY `idx_ip_criado` (`ip`,`criado_em`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `tb_desperdicio`
--
ALTER TABLE `tb_desperdicio`
  MODIFY `id_desperdicio` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de tabela `tb_estoque`
--
ALTER TABLE `tb_estoque`
  MODIFY `id_ingrediente` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=70;

--
-- AUTO_INCREMENT de tabela `tb_fornecedores`
--
ALTER TABLE `tb_fornecedores`
  MODIFY `id_fornecedor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de tabela `tb_funcionarios`
--
ALTER TABLE `tb_funcionarios`
  MODIFY `id_funcionario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT de tabela `tb_gestor`
--
ALTER TABLE `tb_gestor`
  MODIFY `id_gestor` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `tb_item_ficha_tecnica`
--
ALTER TABLE `tb_item_ficha_tecnica`
  MODIFY `id_item_ficha` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=132;

--
-- AUTO_INCREMENT de tabela `tb_itens_pedido`
--
ALTER TABLE `tb_itens_pedido`
  MODIFY `id_itens` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=195;

--
-- AUTO_INCREMENT de tabela `tb_kanban_tarefas`
--
ALTER TABLE `tb_kanban_tarefas`
  MODIFY `id_tarefa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de tabela `tb_layout`
--
ALTER TABLE `tb_layout`
  MODIFY `id_layout` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT de tabela `tb_mesas`
--
ALTER TABLE `tb_mesas`
  MODIFY `id_mesa` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de tabela `tb_pagamentos`
--
ALTER TABLE `tb_pagamentos`
  MODIFY `id_pagamento` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `tb_pedidos`
--
ALTER TABLE `tb_pedidos`
  MODIFY `id_pedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT de tabela `tb_pratos`
--
ALTER TABLE `tb_pratos`
  MODIFY `id_prato` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de tabela `tb_recuperacao_senha`
--
ALTER TABLE `tb_recuperacao_senha`
  MODIFY `id_recuperacao` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `tb_fornecedor_estoque`
--
ALTER TABLE `tb_fornecedor_estoque`
  ADD CONSTRAINT `fk_fe_fornecedor` FOREIGN KEY (`id_fornecedor`) REFERENCES `tb_fornecedores` (`id_fornecedor`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_fe_ingrediente` FOREIGN KEY (`id_ingrediente`) REFERENCES `tb_estoque` (`id_ingrediente`) ON DELETE CASCADE;

--
-- Restrições para tabelas `tb_item_ficha_tecnica`
--
ALTER TABLE `tb_item_ficha_tecnica`
  ADD CONSTRAINT `fk_ficha_prato_cascade` FOREIGN KEY (`id_prato`) REFERENCES `tb_pratos` (`id_prato`) ON DELETE CASCADE;

--
-- Restrições para tabelas `tb_kanban_tarefas`
--
ALTER TABLE `tb_kanban_tarefas`
  ADD CONSTRAINT `fk_kanban_gestor` FOREIGN KEY (`id_gestor`) REFERENCES `tb_gestor` (`id_gestor`) ON DELETE CASCADE;

--
-- Restrições para tabelas `tb_layout`
--
ALTER TABLE `tb_layout`
  ADD CONSTRAINT `fk_layout_gestor` FOREIGN KEY (`id_gestor`) REFERENCES `tb_gestor` (`id_gestor`) ON DELETE CASCADE;

--
-- Restrições para tabelas `tb_pagamentos`
--
ALTER TABLE `tb_pagamentos`
  ADD CONSTRAINT `fk_pagamentos_gestor` FOREIGN KEY (`id_gestor`) REFERENCES `tb_gestor` (`id_gestor`) ON DELETE CASCADE;

--
-- Restrições para tabelas `tb_recuperacao_senha`
--
ALTER TABLE `tb_recuperacao_senha`
  ADD CONSTRAINT `fk_recuperacao_gestor` FOREIGN KEY (`id_gestor`) REFERENCES `tb_gestor` (`id_gestor`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
