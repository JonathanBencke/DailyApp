<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function isLoggedIn() {
    return isset($_SESSION['team_id']);
}

function isAdmin() {
    return isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true;
}

function requireLogin() {
    if (!isLoggedIn()) {
        if (defined('IS_API')) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Não autenticado']);
            exit;
        }
        header('Location: /index.php');
        exit;
    }
}

function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        if (defined('IS_API')) {
            http_response_code(403);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Acesso negado']);
            exit;
        }
        header('Location: /dashboard.php');
        exit;
    }
}

function getTeamId() {
    return $_SESSION['team_id'] ?? null;
}
