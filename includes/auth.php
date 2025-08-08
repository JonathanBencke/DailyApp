<?php
/**
 * Funções de autenticação
 */

function initializeAuth($authFile, $defaultAuth) {
    if (!file_exists($authFile)) {
        file_put_contents($authFile, json_encode($defaultAuth, JSON_PRETTY_PRINT));
    }
    return json_decode(file_get_contents($authFile), true);
}

function processLogin($authConfig) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if ($username === $authConfig['username'] && password_verify($password, $authConfig['password'])) {
            $_SESSION['authenticated'] = true;
            $_SESSION['username'] = $username;
            header('Location: ' . $_SERVER['PHP_SELF']);
            exit;
        } else {
            return "Usuário ou senha incorretos!";
        }
    }
    return null;
}

function processLogout() {
    if (isset($_GET['logout'])) {
        session_destroy();
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    }
}

function isAuthenticated() {
    return isset($_SESSION['authenticated']) && $_SESSION['authenticated'] === true;
}
