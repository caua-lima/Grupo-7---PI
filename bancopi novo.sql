-- phpMyAdmin SQL Dump
-- version 5.0.4
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 21-Nov-2024 às 17:58
-- Versão do servidor: 9.0.1
-- versão do PHP: 7.4.12

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
CREATE DATABASE IF NOT EXISTS `bancopi` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci;
USE `bancopi`;

-- --------------------------------------------------------

--
-- Estrutura da tabela `aulas_falta`
--

CREATE TABLE `aulas_falta` (
  `idaulas_falta` int NOT NULL,
  `num_aulas` varchar(3) NOT NULL,
  `data_aula` date NOT NULL,
  `nome_disciplina` varchar(40) NOT NULL,
  `idform_faltas` int DEFAULT NULL,
  `idcursos` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `aulas_falta_formulario_reposicao`
--

CREATE TABLE `aulas_falta_formulario_reposicao` (
  `idaulas_falta_form_reposicao` int NOT NULL,
  `idaulas_faltas` int DEFAULT NULL,
  `idform_reposicao` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `aulas_reposicao`
--

CREATE TABLE `aulas_reposicao` (
  `idaulas_reposicao` int NOT NULL,
  `data_reposicao` date NOT NULL,
  `nome_disciplina` varchar(40) NOT NULL,
  `horarioinicio` varchar(10) NOT NULL,
  `horariofim` varchar(10) NOT NULL,
  `idform_reposicao` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `aulas_reposicoa_formulario_reposicao`
--

CREATE TABLE `aulas_reposicoa_formulario_reposicao` (
  `idaulas_reposicao_form_reposicao` int NOT NULL,
  `idaulas_reposicao` int DEFAULT NULL,
  `idform_reposicao` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `aulas_semanal_professor`
--

CREATE TABLE `aulas_semanal_professor` (
  `idaula` int NOT NULL,
  `idfuncionario` int NOT NULL,
  `dia_semana` varchar(10) NOT NULL,
  `horario_inicio` time NOT NULL,
  `horario_fim` time NOT NULL,
  `disciplina` varchar(100) NOT NULL,
  `idcursos` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Extraindo dados da tabela `aulas_semanal_professor`
--

INSERT INTO `aulas_semanal_professor` (`idaula`, `idfuncionario`, `dia_semana`, `horario_inicio`, `horario_fim`, `disciplina`, `idcursos`) VALUES
(1, 1, 'SEGUNDA', '19:00:00', '20:40:00', 'Programação Avançada', 1),
(2, 1, 'SEGUNDA', '20:50:00', '22:30:00', 'Programação Avançada', 1),
(3, 1, 'TERÇA', '19:00:00', '20:40:00', 'Design Digital', 1),
(4, 1, 'TERÇA', '20:50:00', '22:30:00', 'Design Digital', 1),
(5, 1, 'QUARTA', '19:00:00', '20:40:00', 'Sistemas Operacionais', 1),
(6, 1, 'QUARTA', '20:50:00', '22:30:00', ' Introdução à Gestão da Produção', 3),
(7, 1, 'QUINTA', '19:00:00', '20:40:00', 'Desenvolvimento Web I', 1),
(8, 1, 'QUINTA', '20:50:00', '22:30:00', 'Desenvolvimento Web I', 1),
(9, 1, 'SEXTA', '19:00:00', '20:40:00', 'Redes de Computadores', 1),
(10, 1, 'SEXTA', '20:50:00', '22:30:00', 'Redes de Computadores', 1),
(11, 1, 'SÁBADO', '09:20:00', '11:00:00', 'Estrutura de Dados', 1),
(12, 1, 'SÁBADO', '11:00:00', '12:50:00', 'Estrutura de Dados', 1);

-- --------------------------------------------------------

INSERT INTO `aulas_semanal_professor` (`idaula`, `idfuncionario`, `dia_semana`, `horario_inicio`, `horario_fim`, `disciplina`, `idcursos`) VALUES
(13, 2, 'SEGUNDA', '19:00:00', '20:40:00', 'Programação Avançada', 1),
(14, 2, 'SEGUNDA', '20:50:00', '22:30:00', 'Programação Avançada', 1),
(15, 2, 'TERÇA', '19:00:00', '20:40:00', 'Design Digital', 1),
(16, 2, 'TERÇA', '20:50:00', '22:30:00', 'Design Digital', 1),
(17, 2, 'QUARTA', '19:00:00', '20:40:00', 'Sistemas Operacionais', 1),
(18, 2, 'QUARTA', '20:50:00', '22:30:00', ' Introdução à Gestão da Produção', 3),
(19, 2, 'QUINTA', '19:00:00', '20:40:00', 'Desenvolvimento Web I', 1),
(20, 2, 'QUINTA', '20:50:00', '22:30:00', 'Desenvolvimento Web I', 1),
(21, 2, 'SEXTA', '19:00:00', '20:40:00', 'Redes de Computadores', 1),
(22, 2, 'SEXTA', '20:50:00', '22:30:00', 'Redes de Computadores', 1),
(23, 2, 'SÁBADO', '09:20:00', '11:00:00', 'Estrutura de Dados', 1),
(24, 2, 'SÁBADO', '11:00:00', '12:50:00', 'Estrutura de Dados', 1);

--
-- Estrutura da tabela `cursos`
--

CREATE TABLE `cursos` (
  `idcursos` int NOT NULL,
  `nome_curso` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Extraindo dados da tabela `cursos`
--

INSERT INTO `cursos` (`idcursos`, `nome_curso`) VALUES
(1, 'DSM'),
(2, 'GE'),
(3, 'GPI'),
(4, 'GTI'),
(5, 'HAE');

-- --------------------------------------------------------

--
-- Estrutura da tabela `formulario_faltas`
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

-- --------------------------------------------------------

--
-- Estrutura da tabela `formulario_faltas_cursos`
--

CREATE TABLE `formulario_faltas_cursos` (
  `idform_faltas_cursos` int NOT NULL,
  `idform_faltas` int DEFAULT NULL,
  `idcursos` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `formulario_reposicao`
--

CREATE TABLE `formulario_reposicao` (
  `idform_reposicao` int NOT NULL,
  `virtude` varchar(45) NOT NULL,
  `data_entrega` date NOT NULL,
  `pdf_form` varchar(255) DEFAULT NULL,
  `pdf_aulas` varchar(45) DEFAULT NULL,
  `situacao` varchar(20) NOT NULL,
  `motivo_indeferimento` varchar(100) DEFAULT NULL,
  `idfuncionario` int DEFAULT NULL,
  `idform_faltas` int DEFAULT NULL,
  `idform_falformulario_faltastas` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `formulario_reposicao_cursos`
--

CREATE TABLE `formulario_reposicao_cursos` (
  `idform_reposicao_cursos` int NOT NULL,
  `idform_reposicao` int DEFAULT NULL,
  `idcursos` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Estrutura da tabela `funcionarios`
--

CREATE TABLE `funcionarios` (
  `idfuncionario` int NOT NULL,
  `nome` varchar(70) NOT NULL,
  `email` varchar(45) NOT NULL,
  `matricula` varchar(45) NOT NULL,
  `funcao` varchar(45) NOT NULL,
  `regime_juridico` varchar(45) DEFAULT NULL,
  `senha` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Extraindo dados da tabela `funcionarios`
--

INSERT INTO `funcionarios` (`idfuncionario`, `nome`, `email`, `matricula`, `funcao`, `regime_juridico`, `senha`) VALUES
(1, 'Tiago Alvez', 'thiago.alves16@fatec.sp.gov.br', '1234567', 'Professor de Ensino Superior', 'CLT', '$2y$10$uLQ1xlwLr8kXOIwM2kIVieZxaKtqIor3mqE0my1svjgXDB/m/r7ty'),
(2, 'marco', 'marco.bubola@fatec.sp.gov.br', '123456789', 'Professor de Ensino Superior', 'CLT', '$2y$10$uLQ1xlwLr8kXOIwM2kIVieZxaKtqIor3mqE0my1svjgXDB/m/r7ty'),
(3, 'Pedro', 'cordenador@fatec.sp.gov.br', '123456789', 'COORDENADOR', 'CLT', '$2y$10$uLQ1xlwLr8kXOIwM2kIVieZxaKtqIor3mqE0my1svjgXDB/m/r7ty');

-- --------------------------------------------------------

--
-- Estrutura da tabela `horas_hae_professor`
--

CREATE TABLE `horas_hae_professor` (
  `idhae` int NOT NULL,
  `idfuncionario` int NOT NULL,
  `dia_semana` varchar(10) NOT NULL,
  `data_atividade` date NOT NULL,
  `horario_inicio` time NOT NULL,
  `horario_fim` time NOT NULL,
  `tipo_atividade` varchar(100) NOT NULL,
  `hae_total` int NOT NULL,
  `hae_usadas` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Extraindo dados da tabela `horas_hae_professor`
--

INSERT INTO `horas_hae_professor` (`idhae`, `idfuncionario`, `dia_semana`, `data_atividade`, `horario_inicio`, `horario_fim`, `tipo_atividade`, `hae_total`, `hae_usadas`) VALUES
(1, 1, 'Segunda', '2024-11-13', '10:00:00', '12:00:00', 'Planejamento de Aulas', 40, 2),
(2, 1, 'TERÇA', '2024-11-14', '14:00:00', '16:00:00', 'Correção de Provas', 40, 2),
(3, 1, 'Quarta', '2024-11-15', '17:00:00', '18:30:00', 'Atendimento a Alunos', 40, 2),
(4, 1, 'Sexta', '2024-11-17', '08:00:00', '10:00:00', 'Revisão de Conteúdos', 40, 2),
(5, 1, 'segunda', '2024-11-11', '21:13:00', '21:13:00', 'CORRIGIR FALTAS', 40, 0);


INSERT INTO `horas_hae_professor` (`idhae`, `idfuncionario`, `dia_semana`, `data_atividade`, `horario_inicio`, `horario_fim`, `tipo_atividade`, `hae_total`, `hae_usadas`) VALUES
(6, 2, 'Segunda', '2024-11-13', '10:00:00', '12:00:00', 'Planejamento de Aulas', 40, 2),
(7, 2, 'TERÇA', '2024-11-14', '14:00:00', '16:00:00', 'Correção de Provas', 40, 2),
(8, 2, 'Quarta', '2024-11-15', '17:00:00', '18:30:00', 'Atendimento a Alunos', 40, 2),
(9, 2, 'Sexta', '2024-11-17', '08:00:00', '10:00:00', 'Revisão de Conteúdos', 40, 2),
(10, 2, 'segunda', '2024-11-11', '21:13:00', '21:13:00', 'CORRIGIR FALTAS', 40, 0);

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `aulas_falta`
--
ALTER TABLE `aulas_falta`
  ADD PRIMARY KEY (`idaulas_falta`),
  ADD KEY `idform_faltas_idx` (`idform_faltas`),
  ADD KEY `idcursos_idx` (`idcursos`);

--
-- Índices para tabela `aulas_reposicao`
--
ALTER TABLE `aulas_reposicao`
  ADD PRIMARY KEY (`idaulas_reposicao`),
  ADD KEY `idform_reposicao` (`idform_reposicao`);

--
-- Índices para tabela `aulas_reposicoa_formulario_reposicao`
--
ALTER TABLE `aulas_reposicoa_formulario_reposicao`
  ADD PRIMARY KEY (`idaulas_reposicao_form_reposicao`),
  ADD KEY `idform_reposicao` (`idform_reposicao`),
  ADD KEY `aulas_falta_formulario_reposicao_ibfk_1_idx` (`idaulas_reposicao`);

--
-- Índices para tabela `aulas_semanal_professor`
--
ALTER TABLE `aulas_semanal_professor`
  ADD PRIMARY KEY (`idaula`),
  ADD KEY `idfuncionario` (`idfuncionario`),
  ADD KEY `idcursos` (`idcursos`);

--
-- Índices para tabela `cursos`
--
ALTER TABLE `cursos`
  ADD PRIMARY KEY (`idcursos`);

--
-- Índices para tabela `formulario_faltas`
--
ALTER TABLE `formulario_faltas`
  ADD PRIMARY KEY (`idform_faltas`),
  ADD KEY `idfuncionario` (`idfuncionario`);

--
-- Índices para tabela `formulario_faltas_cursos`
--
ALTER TABLE `formulario_faltas_cursos`
  ADD PRIMARY KEY (`idform_faltas_cursos`),
  ADD KEY `idform_faltas` (`idform_faltas`),
  ADD KEY `idcursos` (`idcursos`);

--
-- Índices para tabela `formulario_reposicao`
--
ALTER TABLE `formulario_reposicao`
  ADD PRIMARY KEY (`idform_reposicao`),
  ADD KEY `idfuncionario` (`idfuncionario`),
  ADD KEY `idform_faltas` (`idform_faltas`);

--
-- Índices para tabela `formulario_reposicao_cursos`
--
ALTER TABLE `formulario_reposicao_cursos`
  ADD PRIMARY KEY (`idform_reposicao_cursos`),
  ADD KEY `idform_reposicao` (`idform_reposicao`),
  ADD KEY `idcursos` (`idcursos`);

--
-- Índices para tabela `funcionarios`
--
ALTER TABLE `funcionarios`
  ADD PRIMARY KEY (`idfuncionario`);

--
-- Índices para tabela `horas_hae_professor`
--
ALTER TABLE `horas_hae_professor`
  ADD PRIMARY KEY (`idhae`),
  ADD KEY `idfuncionario` (`idfuncionario`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `aulas_falta`
--
ALTER TABLE `aulas_falta`
  MODIFY `idaulas_falta` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `aulas_reposicao`
--
ALTER TABLE `aulas_reposicao`
  MODIFY `idaulas_reposicao` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `aulas_reposicoa_formulario_reposicao`
--
ALTER TABLE `aulas_reposicoa_formulario_reposicao`
  MODIFY `idaulas_reposicao_form_reposicao` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de tabela `aulas_semanal_professor`
--
ALTER TABLE `aulas_semanal_professor`
  MODIFY `idaula` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de tabela `cursos`
--
ALTER TABLE `cursos`
  MODIFY `idcursos` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `formulario_faltas`
--
ALTER TABLE `formulario_faltas`
  MODIFY `idform_faltas` int NOT NULL AUTO_INCREMENT;

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
  MODIFY `idfuncionario` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `horas_hae_professor`
--
ALTER TABLE `horas_hae_professor`
  MODIFY `idhae` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- Restrições para despejos de tabelas
--

--
-- Limitadores para a tabela `aulas_falta`
--
ALTER TABLE `aulas_falta`
  ADD CONSTRAINT `idcursos` FOREIGN KEY (`idcursos`) REFERENCES `cursos` (`idcursos`),
  ADD CONSTRAINT `idform_faltas` FOREIGN KEY (`idform_faltas`) REFERENCES `formulario_faltas` (`idform_faltas`);

--
-- Limitadores para a tabela `aulas_reposicao`
--
ALTER TABLE `aulas_reposicao`
  ADD CONSTRAINT `aulas_reposicao_ibfk_1` FOREIGN KEY (`idform_reposicao`) REFERENCES `formulario_reposicao` (`idform_reposicao`);

--
-- Limitadores para a tabela `aulas_reposicoa_formulario_reposicao`
--
ALTER TABLE `aulas_reposicoa_formulario_reposicao`
  ADD CONSTRAINT `aulas_reposicoa_formulario_reposicao_ibfk_1` FOREIGN KEY (`idaulas_reposicao`) REFERENCES `aulas_reposicao` (`idaulas_reposicao`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `aulas_reposicoa_formulario_reposicao_ibfk_2` FOREIGN KEY (`idform_reposicao`) REFERENCES `formulario_reposicao` (`idform_reposicao`);

--
-- Limitadores para a tabela `formulario_faltas`
--
ALTER TABLE `formulario_faltas`
  ADD CONSTRAINT `formulario_faltas_ibfk_1` FOREIGN KEY (`idfuncionario`) REFERENCES `funcionarios` (`idfuncionario`);

--
-- Limitadores para a tabela `formulario_faltas_cursos`
--
ALTER TABLE `formulario_faltas_cursos`
  ADD CONSTRAINT `formulario_faltas_cursos_ibfk_1` FOREIGN KEY (`idform_faltas`) REFERENCES `formulario_faltas` (`idform_faltas`),
  ADD CONSTRAINT `formulario_faltas_cursos_ibfk_2` FOREIGN KEY (`idcursos`) REFERENCES `cursos` (`idcursos`);

--
-- Limitadores para a tabela `formulario_reposicao`
--
ALTER TABLE `formulario_reposicao`
  ADD CONSTRAINT `formulario_reposicao_ibfk_1` FOREIGN KEY (`idfuncionario`) REFERENCES `funcionarios` (`idfuncionario`),
  ADD CONSTRAINT `formulario_reposicao_ibfk_2` FOREIGN KEY (`idform_faltas`) REFERENCES `formulario_faltas` (`idform_faltas`),
  ADD CONSTRAINT `formulario_reposicao_ibfk_3` FOREIGN KEY (`idform_faltas`) REFERENCES `formulario_faltas` (`idform_faltas`);

--
-- Limitadores para a tabela `formulario_reposicao_cursos`
--
ALTER TABLE `formulario_reposicao_cursos`
  ADD CONSTRAINT `formulario_reposicao_cursos_ibfk_1` FOREIGN KEY (`idform_reposicao`) REFERENCES `formulario_reposicao` (`idform_reposicao`),
  ADD CONSTRAINT `formulario_reposicao_cursos_ibfk_2` FOREIGN KEY (`idcursos`) REFERENCES `cursos` (`idcursos`);

--
-- Limitadores para a tabela `horas_hae_professor`
--
ALTER TABLE `horas_hae_professor`
  ADD CONSTRAINT `horas_hae_professor_ibfk_1` FOREIGN KEY (`idfuncionario`) REFERENCES `funcionarios` (`idfuncionario`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
