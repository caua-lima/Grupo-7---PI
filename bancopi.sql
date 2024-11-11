-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Tempo de geração: 08/11/2024 às 00:13
-- Versão do servidor: 8.0.39
-- Versão do PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
-- Estrutura para tabela `aulas_falta`
--

CREATE TABLE `aulas_falta` (
  `idaulas_falta` int NOT NULL,
  `num_aulas` varchar(3) NOT NULL,
  `data_aula` date NOT NULL,
  `nome_disciplina` varchar(40) NOT NULL,
  `idform_faltas` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `aulas_falta`
--

INSERT INTO `aulas_falta` (`idaulas_falta`, `num_aulas`, `data_aula`, `nome_disciplina`, `idform_faltas`) VALUES
(1, '2', '2024-11-06', 'Design Digital', 1),
(2, '2', '2024-11-06', ' Introdução à Gestão da Produção', 1),
(3, '2', '2024-11-04', 'Sistemas Operacionais', 2),
(4, '2', '2024-11-06', 'Design Digital', 3),
(5, '2', '2024-11-06', 'Design Digital', 3);

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

--
-- Despejando dados para a tabela `aulas_reposicao`
--

INSERT INTO `aulas_reposicao` (`idaulas_reposicao`, `data_reposicao`, `nome_disciplina`, `horarioinicio`, `horariofim`) VALUES
(1, '2024-11-09', 'Design Digital', '01:02', '01:04'),
(2, '2024-11-19', ' Introdução à Gestão da Produção', '22:01', '23:01');

-- --------------------------------------------------------

--
-- Estrutura para tabela `aulas_reposicoa_formulario_reposicao`
--

CREATE TABLE `aulas_reposicoa_formulario_reposicao` (
  `idaulas_reposicao_form_reposicao` int NOT NULL,
  `idaulas_reposicao` int DEFAULT NULL,
  `idform_reposicao` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `aulas_reposicoa_formulario_reposicao`
--

INSERT INTO `aulas_reposicoa_formulario_reposicao` (`idaulas_reposicao_form_reposicao`, `idaulas_reposicao`, `idform_reposicao`) VALUES
(1, 1, 1),
(2, 2, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `aulas_semanal_professor`
--

CREATE TABLE `aulas_semanal_professor` (
  `idaula` int NOT NULL,
  `idfuncionario` int NOT NULL,
  `dia_semana` varchar(10) NOT NULL,
  `horario_inicio` time NOT NULL,
  `horario_fim` time NOT NULL,
  `disciplina` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `aulas_semanal_professor`
--

INSERT INTO `aulas_semanal_professor` (`idaula`, `idfuncionario`, `dia_semana`, `horario_inicio`, `horario_fim`, `disciplina`) VALUES
(1, 1, 'SEGUNDA', '19:00:00', '20:40:00', 'Programação Avançada'),
(2, 1, 'SEGUNDA', '20:50:00', '22:30:00', 'Programação Avançada'),
(3, 1, 'TERÇA', '19:00:00', '20:40:00', 'Design Digital'),
(4, 1, 'TERÇA', '20:50:00', '22:30:00', 'Design Digital'),
(5, 1, 'QUARTA', '19:00:00', '20:40:00', 'Sistemas Operacionais'),
(6, 1, 'QUARTA', '20:50:00', '22:30:00', ' Introdução à Gestão da Produção'),
(7, 1, 'QUINTA', '19:00:00', '20:40:00', 'Desenvolvimento Web I'),
(8, 1, 'QUINTA', '20:50:00', '22:30:00', 'Desenvolvimento Web I'),
(9, 1, 'SEXTA', '19:00:00', '20:40:00', 'Redes de Computadores'),
(10, 1, 'SEXTA', '20:50:00', '22:30:00', 'Redes de Computadores'),
(11, 1, 'SÁBADO', '09:20:00', '11:00:00', 'Estrutura de Dados'),
(12, 1, 'SÁBADO', '11:00:00', '12:50:00', 'Estrutura de Dados');

-- --------------------------------------------------------

--
-- Estrutura para tabela `cursos`
--

CREATE TABLE `cursos` (
  `idcursos` int NOT NULL,
  `nome_curso` varchar(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `cursos`
--

INSERT INTO `cursos` (`idcursos`, `nome_curso`) VALUES
(1, 'DSM'),
(2, 'GE'),
(3, 'GPI'),
(4, 'GTI'),
(5, 'HAE');

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
(1, '2024-11-06', '2024-11-06', '672aa19622c6b-671bfb7a0d6c9-formulario_falta.pdf', 'nascimento-filho', NULL, 'Proposta Enviada', NULL, 1),
(2, '2024-11-04', '2024-11-04', '672aa29fe1c5a-671bfb7a0d6c9-formulario_falta.pdf', 'licenca-saude', NULL, 'Aguardando Reposição', NULL, 1),
(3, '2024-11-06', '2024-11-06', '672abf34e8cf9-671bfb7a0d6c9-formulario_falta.pdf', 'casamento', NULL, 'Aguardando Reposição', NULL, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `formulario_faltas_cursos`
--

CREATE TABLE `formulario_faltas_cursos` (
  `idform_faltas_cursos` int NOT NULL,
  `idform_faltas` int DEFAULT NULL,
  `idcursos` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `formulario_faltas_cursos`
--

INSERT INTO `formulario_faltas_cursos` (`idform_faltas_cursos`, `idform_faltas`, `idcursos`) VALUES
(1, 1, 1),
(2, 1, 2),
(3, 2, 1),
(4, 3, 1),
(5, 3, 2);

-- --------------------------------------------------------

--
-- Estrutura para tabela `formulario_reposicao`
--

CREATE TABLE `formulario_reposicao` (
  `idform_reposicao` int NOT NULL,
  `virtude` varchar(45) NOT NULL,
  `data_entrega` date NOT NULL,
  `pdf_form` varchar(255) DEFAULT NULL,
  `pdf_aulas` varchar(45) DEFAULT NULL,
  `situacao` varchar(20) NOT NULL,
  `motivo_indeferimento` varchar(100) DEFAULT NULL,
  `idfuncionario` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `formulario_reposicao`
--

INSERT INTO `formulario_reposicao` (`idform_reposicao`, `virtude`, `data_entrega`, `pdf_form`, `pdf_aulas`, `situacao`, `motivo_indeferimento`, `idfuncionario`) VALUES
(1, 'nascimento-filho', '2024-11-06', NULL, NULL, 'Proposta Enviada', NULL, 1);

-- --------------------------------------------------------

--
-- Estrutura para tabela `formulario_reposicao_cursos`
--

CREATE TABLE `formulario_reposicao_cursos` (
  `idform_reposicao_cursos` int NOT NULL,
  `idform_reposicao` int DEFAULT NULL,
  `idcursos` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `formulario_reposicao_cursos`
--

INSERT INTO `formulario_reposicao_cursos` (`idform_reposicao_cursos`, `idform_reposicao`, `idcursos`) VALUES
(1, 1, 1),
(2, 1, 2);

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
  `regime_juridico` varchar(45) DEFAULT NULL,
  `senha` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Despejando dados para a tabela `funcionarios`
--

INSERT INTO `funcionarios` (`idfuncionario`, `nome`, `email`, `matricula`, `funcao`, `regime_juridico`, `senha`) VALUES
(1, 'Tiago Alvez', 'thiago.alves16@fatec.sp.gov.br', '1234567', 'Professor de Ensino Superior', 'CLT', '');

-- --------------------------------------------------------

--
-- Estrutura para tabela `horas_hae_professor`
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
-- Despejando dados para a tabela `horas_hae_professor`
--

INSERT INTO `horas_hae_professor` (`idhae`, `idfuncionario`, `dia_semana`, `data_atividade`, `horario_inicio`, `horario_fim`, `tipo_atividade`, `hae_total`, `hae_usadas`) VALUES
(1, 1, 'Segunda', '2024-11-13', '10:00:00', '12:00:00', 'Planejamento de Aulas', 40, 2),
(2, 1, 'TERÇA', '2024-11-14', '14:00:00', '16:00:00', 'Correção de Provas', 40, 2),
(3, 1, 'Quarta', '2024-11-15', '17:00:00', '18:30:00', 'Atendimento a Alunos', 40, 2),
(4, 1, 'Sexta', '2024-11-17', '08:00:00', '10:00:00', 'Revisão de Conteúdos', 40, 2),
(11, 1, 'segunda', '2024-11-06', '21:00:00', '22:00:00', 'correção', 40, 0),
(12, 1, 'Sábado', '2024-11-09', '01:02:00', '01:04:00', 'Reposição de Aula', 0, 0),
(13, 1, 'Terça', '2024-11-19', '22:01:00', '23:01:00', 'Reposição de Aula', 0, 0);

--
-- Índices para tabelas despejadas
--

--
-- Índices de tabela `aulas_falta`
--
ALTER TABLE `aulas_falta`
  ADD PRIMARY KEY (`idaulas_falta`),
  ADD KEY `idform_faltas_idx` (`idform_faltas`);

--
-- Índices de tabela `aulas_reposicao`
--
ALTER TABLE `aulas_reposicao`
  ADD PRIMARY KEY (`idaulas_reposicao`);

--
-- Índices de tabela `aulas_reposicoa_formulario_reposicao`
--
ALTER TABLE `aulas_reposicoa_formulario_reposicao`
  ADD PRIMARY KEY (`idaulas_reposicao_form_reposicao`),
  ADD KEY `idform_reposicao` (`idform_reposicao`),
  ADD KEY `aulas_falta_formulario_reposicao_ibfk_1_idx` (`idaulas_reposicao`);

--
-- Índices de tabela `aulas_semanal_professor`
--
ALTER TABLE `aulas_semanal_professor`
  ADD PRIMARY KEY (`idaula`),
  ADD KEY `idfuncionario` (`idfuncionario`);

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
-- Índices de tabela `horas_hae_professor`
--
ALTER TABLE `horas_hae_professor`
  ADD PRIMARY KEY (`idhae`),
  ADD KEY `idfuncionario` (`idfuncionario`);

--
-- AUTO_INCREMENT para tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `aulas_falta`
--
ALTER TABLE `aulas_falta`
  MODIFY `idaulas_falta` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `aulas_reposicao`
--
ALTER TABLE `aulas_reposicao`
  MODIFY `idaulas_reposicao` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `aulas_reposicoa_formulario_reposicao`
--
ALTER TABLE `aulas_reposicoa_formulario_reposicao`
  MODIFY `idaulas_reposicao_form_reposicao` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
  MODIFY `idform_faltas` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de tabela `formulario_faltas_cursos`
--
ALTER TABLE `formulario_faltas_cursos`
  MODIFY `idform_faltas_cursos` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de tabela `formulario_reposicao`
--
ALTER TABLE `formulario_reposicao`
  MODIFY `idform_reposicao` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `formulario_reposicao_cursos`
--
ALTER TABLE `formulario_reposicao_cursos`
  MODIFY `idform_reposicao_cursos` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de tabela `funcionarios`
--
ALTER TABLE `funcionarios`
  MODIFY `idfuncionario` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de tabela `horas_hae_professor`
--
ALTER TABLE `horas_hae_professor`
  MODIFY `idhae` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- Restrições para tabelas despejadas
--

--
-- Restrições para tabelas `aulas_falta`
--
ALTER TABLE `aulas_falta`
  ADD CONSTRAINT `idform_faltas` FOREIGN KEY (`idform_faltas`) REFERENCES `formulario_faltas` (`idform_faltas`);

--
-- Restrições para tabelas `aulas_reposicoa_formulario_reposicao`
--
ALTER TABLE `aulas_reposicoa_formulario_reposicao`
  ADD CONSTRAINT `aulas_reposicoa_formulario_reposicao_ibfk_1` FOREIGN KEY (`idaulas_reposicao`) REFERENCES `aulas_reposicao` (`idaulas_reposicao`),
  ADD CONSTRAINT `aulas_reposicoa_formulario_reposicao_ibfk_2` FOREIGN KEY (`idform_reposicao`) REFERENCES `formulario_reposicao` (`idform_reposicao`);

--
-- Restrições para tabelas `aulas_semanal_professor`
--
ALTER TABLE `aulas_semanal_professor`
  ADD CONSTRAINT `aulas_semanal_professor_ibfk_1` FOREIGN KEY (`idfuncionario`) REFERENCES `funcionarios` (`idfuncionario`);

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

--
-- Restrições para tabelas `horas_hae_professor`
--
ALTER TABLE `horas_hae_professor`
  ADD CONSTRAINT `horas_hae_professor_ibfk_1` FOREIGN KEY (`idfuncionario`) REFERENCES `funcionarios` (`idfuncionario`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
