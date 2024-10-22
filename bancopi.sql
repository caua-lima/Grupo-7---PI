-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 19/10/2024 às 02:36
-- Versão do servidor: 8.0.39
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Banco de dados: `bancopi`
--

-- --------------------------------------------------------

--
-- Estrutura para tabela `aulas_falta`
--

CREATE TABLE `aulas_falta` (
  `idaulas_falta` int NOT NULL,
  `num_aulas` varchar(3) NOT NULL,
  `data_aula` date NOT NULL,
  `nome_disciplina` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `aulas_falta_formulario_reposicao`
--

CREATE TABLE `aulas_falta_formulario_reposicao` (
  `idaulas_falta_form_reposicao` int NOT NULL,
  `idaulas_faltas` int DEFAULT NULL,
  `idform_reposicao` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `aulas_reposicao`
--

CREATE TABLE `aulas_reposicao` (
  `idaulas_reposicao` int NOT NULL,
  `data_reposicao` date NOT NULL,
  `nome_disciplina` varchar(40) NOT NULL,
  `horarioinicio` varchar(10) NOT NULL,
  `horariofim` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `aulas_reposicao_formulario_reposicao`
--

CREATE TABLE `aulas_reposicao_formulario_reposicao` (
  `idaulas_reposicao_formulario_reposicao` int NOT NULL,
  `idaulas_reposicao` int DEFAULT NULL,
  `idform_reposicao` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `cursos`
--

CREATE TABLE `cursos` (
  `idcursos` int NOT NULL,
  `nome_curso` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `formulario_faltas`
--

CREATE TABLE `formulario_faltas` (
  `idform_faltas` int NOT NULL,
  `datainicio` varchar(20) NOT NULL,
  `datafim` varchar(20) NOT NULL,
  `pdf_atestado` varchar(255) NOT NULL,
  `motivo_falta` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL,
  `pdf_form` varchar(255) DEFAULT NULL,
  `situacao` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci DEFAULT NULL,
  `motivo_indeferimento` varchar(100) DEFAULT NULL,
  `idfuncionario` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `formulario_faltas`
--

INSERT INTO `formulario_faltas` (`idform_faltas`, `datainicio`, `datafim`, `pdf_atestado`, `motivo_falta`, `pdf_form`, `situacao`, `motivo_indeferimento`, `idfuncionario`) VALUES
(1, '2024-10-03', '2024-10-02', '6712f3dc05040-ATIVIDADE _ GERENCIAMENTO DE CLIENTES, USUARIOS e PRODUTOS.pdf', 'falta-injustificada', NULL, NULL, NULL, NULL),
(2, '2024-10-24', '2024-10-24', '6712f43b14c84-2435e130-58b1-45d6-9b12-20f9d04b2baa.pdf', 'falecimento-conjuge', NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estrutura para tabela `formulario_faltas_cursos`
--

CREATE TABLE `formulario_faltas_cursos` (
  `idform_faltas_cursos` int NOT NULL,
  `idform_faltas` int DEFAULT NULL,
  `idcursos` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `formulario_reposicao`
--

CREATE TABLE `formulario_reposicao` (
  `idform_reposicao` int NOT NULL,
  `virtude` varchar(45) NOT NULL,
  `data_entrega` date NOT NULL,
  `pdf_form` varchar(255) NOT NULL,
  `pdf_aulas` varchar(45) NOT NULL,
  `situacao` varchar(20) NOT NULL,
  `motivo_indeferimento` varchar(100) DEFAULT NULL,
  `idfuncionario` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `formulario_reposicao_cursos`
--

CREATE TABLE `formulario_reposicao_cursos` (
  `idform_reposicao_cursos` int NOT NULL,
  `idform_reposicao` int DEFAULT NULL,
  `idcursos` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura para tabela `funcionarios`
--

CREATE TABLE `funcionarios` (
  `idfuncionario` int NOT NULL,
  `nome` varchar(70) NOT NULL,
  `email` varchar(45) NOT NULL,
  `matricula` varchar(45) NOT NULL,
  `funcao` varchar(45) NOT NULL,
  `regime_juridico` varchar(45) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `aulas_falta`
--
ALTER TABLE `aulas_falta`
  ADD PRIMARY KEY (`idaulas_falta`);

--
-- Índices de tabela `aulas_falta_formulario_reposicao`
--
ALTER TABLE `aulas_falta_formulario_reposicao`
  ADD PRIMARY KEY (`idaulas_falta_form_reposicao`),
  ADD KEY `idaulas_faltas` (`idaulas_faltas`),
  ADD KEY `idform_reposicao` (`idform_reposicao`);

--
-- Índices de tabela `aulas_reposicao`
--
ALTER TABLE `aulas_reposicao`
  ADD PRIMARY KEY (`idaulas_reposicao`);

--
-- Índices de tabela `aulas_reposicao_formulario_reposicao`
--
ALTER TABLE `aulas_reposicao_formulario_reposicao`
  ADD PRIMARY KEY (`idaulas_reposicao_formulario_reposicao`),
  ADD KEY `idaulas_reposicao` (`idaulas_reposicao`),
  ADD KEY `idform_reposicao` (`idform_reposicao`);

--
-- Índices de tabela `cursos`
--
ALTER TABLE `cursos`
  ADD PRIMARY KEY (`idcursos`);

--
-- Índices de tabela `formulario_faltas`
--
ALTER TABLE `formulario_faltas`
  ADD PRIMARY KEY (`idform_faltas`),
  ADD KEY `idfuncionario` (`idfuncionario`);

--
-- Índices de tabela `formulario_faltas_cursos`
--
ALTER TABLE `formulario_faltas_cursos`
  ADD PRIMARY KEY (`idform_faltas_cursos`),
  ADD KEY `idform_faltas` (`idform_faltas`),
  ADD KEY `idcursos` (`idcursos`);

--
-- Índices de tabela `formulario_reposicao`
--
ALTER TABLE `formulario_reposicao`
  ADD PRIMARY KEY (`idform_reposicao`),
  ADD KEY `idfuncionario` (`idfuncionario`);

--
-- Índices de tabela `formulario_reposicao_cursos`
--
ALTER TABLE `formulario_reposicao_cursos`
  ADD PRIMARY KEY (`idform_reposicao_cursos`),
  ADD KEY `idform_reposicao` (`idform_reposicao`),
  ADD KEY `idcursos` (`idcursos`);

--
-- Índices de tabela `funcionarios`
--
ALTER TABLE `funcionarios`
  ADD PRIMARY KEY (`idfuncionario`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `aulas_falta`
--
ALTER TABLE `aulas_falta`
  MODIFY `idaulas_falta` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `aulas_falta_formulario_reposicao`
--
ALTER TABLE `aulas_falta_formulario_reposicao`
  MODIFY `idaulas_falta_form_reposicao` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `aulas_reposicao`
--
ALTER TABLE `aulas_reposicao`
  MODIFY `idaulas_reposicao` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `aulas_reposicao_formulario_reposicao`
--
ALTER TABLE `aulas_reposicao_formulario_reposicao`
  MODIFY `idaulas_reposicao_formulario_reposicao` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `cursos`
--
ALTER TABLE `cursos`
  MODIFY `idcursos` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `formulario_faltas`
--
ALTER TABLE `formulario_faltas`
  MODIFY `idform_faltas` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `formulario_faltas_cursos`
--
ALTER TABLE `formulario_faltas_cursos`
  MODIFY `idform_faltas_cursos` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `formulario_reposicao`
--
ALTER TABLE `formulario_reposicao`
  MODIFY `idform_reposicao` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `formulario_reposicao_cursos`
--
ALTER TABLE `formulario_reposicao_cursos`
  MODIFY `idform_reposicao_cursos` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `funcionarios`
--
ALTER TABLE `funcionarios`
  MODIFY `idfuncionario` int NOT NULL AUTO_INCREMENT;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `aulas_falta_formulario_reposicao`
--
ALTER TABLE `aulas_falta_formulario_reposicao`
  ADD CONSTRAINT `aulas_falta_formulario_reposicao_ibfk_1` FOREIGN KEY (`idaulas_faltas`) REFERENCES `aulas_falta` (`idaulas_falta`),
  ADD CONSTRAINT `aulas_falta_formulario_reposicao_ibfk_2` FOREIGN KEY (`idform_reposicao`) REFERENCES `formulario_reposicao` (`idform_reposicao`);

--
-- Restrições para tabelas `aulas_reposicao_formulario_reposicao`
--
ALTER TABLE `aulas_reposicao_formulario_reposicao`
  ADD CONSTRAINT `aulas_reposicao_formulario_reposicao_ibfk_1` FOREIGN KEY (`idaulas_reposicao`) REFERENCES `aulas_reposicao` (`idaulas_reposicao`),
  ADD CONSTRAINT `aulas_reposicao_formulario_reposicao_ibfk_2` FOREIGN KEY (`idform_reposicao`) REFERENCES `formulario_reposicao` (`idform_reposicao`);

--
-- Restrições para tabelas `formulario_faltas`
--
ALTER TABLE `formulario_faltas`
  ADD CONSTRAINT `formulario_faltas_ibfk_1` FOREIGN KEY (`idfuncionario`) REFERENCES `funcionarios` (`idfuncionario`);

--
-- Restrições para tabelas `formulario_faltas_cursos`
--
ALTER TABLE `formulario_faltas_cursos`
  ADD CONSTRAINT `formulario_faltas_cursos_ibfk_1` FOREIGN KEY (`idform_faltas`) REFERENCES `formulario_faltas` (`idform_faltas`),
  ADD CONSTRAINT `formulario_faltas_cursos_ibfk_2` FOREIGN KEY (`idcursos`) REFERENCES `cursos` (`idcursos`);

--
-- Restrições para tabelas `formulario_reposicao`
--
ALTER TABLE `formulario_reposicao`
  ADD CONSTRAINT `formulario_reposicao_ibfk_1` FOREIGN KEY (`idfuncionario`) REFERENCES `funcionarios` (`idfuncionario`);

--
-- Restrições para tabelas `formulario_reposicao_cursos`
--
ALTER TABLE `formulario_reposicao_cursos`
  ADD CONSTRAINT `formulario_reposicao_cursos_ibfk_1` FOREIGN KEY (`idform_reposicao`) REFERENCES `formulario_reposicao` (`idform_reposicao`),
  ADD CONSTRAINT `formulario_reposicao_cursos_ibfk_2` FOREIGN KEY (`idcursos`) REFERENCES `cursos` (`idcursos`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
