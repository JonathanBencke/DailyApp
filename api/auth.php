<?php
define('IS_API', true);
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/data.php';
require_once __DIR__ . '/../includes/auth_check.php';

$method = getMethod();

if ($method === 'POST') {
    $body = getBody();
    $action = $body['action'] ?? '';

    if ($action === 'logout') {
        session_destroy();
        jsonResponse(['ok' => true]);
    }

    $teamName = trim($body['team'] ?? '');
    $password = $body['password'] ?? '';

    if (!$teamName || !$password) {
        errorResponse('Time e senha são obrigatórios');
    }

    // Admin login
    $adminPassword = 'admin123'; // Altere após instalação
    if ($teamName === 'admin' && $password === $adminPassword) {
        $_SESSION['is_admin'] = true;
        $_SESSION['team_id'] = 'admin';
        $_SESSION['team_name'] = 'Administrador';
        jsonResponse(['ok' => true, 'redirect' => '/admin/']);
    }

    $teams = dataRead('teams');
    $team = null;
    foreach ($teams as $t) {
        if (strtolower($t['name']) === strtolower($teamName)) {
            $team = $t;
            break;
        }
    }

    if (!$team || !password_verify($password, $team['password'])) {
        errorResponse('Time ou senha incorretos', 401);
    }

    $_SESSION['team_id'] = $team['id'];
    $_SESSION['team_name'] = $team['name'];
    $_SESSION['is_admin'] = false;

    jsonResponse(['ok' => true, 'redirect' => '/dashboard.php']);
}

errorResponse('Método não permitido', 405);
