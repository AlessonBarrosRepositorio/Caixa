<?php
// Inicia a sessão
session_start();

// Senha configurada (altere para a senha que desejar)
$senha_correta = "caixa2024"; // Mude para a senha que quiser

// Verifica se a senha foi enviada
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $senha_digitada = isset($_POST['senha']) ? trim($_POST['senha']) : '';
    
    // Verifica se a senha está correta
    if ($senha_digitada === $senha_correta) {
        // Define a sessão como logada
        $_SESSION['logado'] = true;
        $_SESSION['ultimo_acesso'] = time();
        
        // Redireciona para a página principal
        header("Location: pagina.php");
        exit();
    } else {
        // Senha incorreta, redireciona de volta com erro
        header("Location: login.php?erro=1");
        exit();
    }
} else {
    // Se acessar diretamente, volta para o login
    header("Location: login.php");
    exit();
}
?>