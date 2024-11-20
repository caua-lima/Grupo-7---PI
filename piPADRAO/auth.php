<?php
session_start();
if (!isset($_SESSION['idfuncionario'])) {
  // Redireciona para a página de login
  header('Location: index.php');
  exit;
}