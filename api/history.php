<?php
define('IS_API', true);
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/data.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireLogin();
$teamId = getTeamId();
$method = getMethod();

if ($method === 'GET') {
    $limit  = max(1, min(200, (int)($_GET['limit']  ?? 30)));
    $offset = max(0, (int)($_GET['offset'] ?? 0));
    $month  = $_GET['month'] ?? ''; // formato YYYY-MM

    $history = dataRead('history');
    $teamHistory = array_values(array_filter($history, function ($h) use ($teamId, $month) {
        if ($h['team_id'] !== $teamId) return false;
        if ($month && strpos($h['date'], $month) !== 0) return false;
        return true;
    }));

    usort($teamHistory, function ($a, $b) {
        if ($b['date'] !== $a['date']) return strcmp($b['date'], $a['date']);
        return strcmp($b['id'], $a['id']);
    });

    $total = count($teamHistory);
    $items = $month ? $teamHistory : array_slice($teamHistory, $offset, $limit);

    jsonResponse(['total' => $total, 'items' => $items]);
}

errorResponse('Método não permitido', 405);
