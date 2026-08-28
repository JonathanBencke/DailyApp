<?php
define('IS_API', true);
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/data.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireLogin();
$teamId = getTeamId();
$method = getMethod();
$workdays = dataReadMap('workdays');

if ($method === 'GET') {
    $teamWorkdays = $workdays[$teamId] ?? ['days' => [1, 2, 3, 4, 5]];
    jsonResponse($teamWorkdays);
}

if ($method === 'POST') {
    $body = getBody();
    $days = $body['days'] ?? [];

    if (!is_array($days)) errorResponse('days deve ser um array');

    $days = array_values(array_filter(array_map('intval', $days), fn($d) => $d >= 1 && $d <= 7));
    sort($days);

    $workdays[$teamId] = ['days' => $days];
    dataWriteMap('workdays', $workdays);

    jsonResponse(['ok' => true, 'days' => $days]);
}

errorResponse('Método não permitido', 405);
