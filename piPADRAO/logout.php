<?php
session_start();
session_destroy(); // Encerra a sessão
header('Location: index.php');
exit;