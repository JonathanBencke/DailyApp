<?php
define('IS_API', true);
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/data.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireLogin();
$teamId = getTeamId();
$method = getMethod();
$holidays = dataRead('holidays');
$teamHolidays = filterBy($holidays, 'team_id', $teamId);

if ($method === 'GET') {
    usort($teamHolidays, fn($a, $b) => strcmp($a['date'], $b['date']));
    jsonResponse($teamHolidays);
}

if ($method === 'POST') {
    $body = getBody();
    $date = $body['date'] ?? '';
    $name = trim($body['name'] ?? '');

    if (!$date || !$name) errorResponse('Data e nome são obrigatórios');
    if (!isValidDate($date)) errorResponse('Data inválida (use YYYY-MM-DD)');

    // Verificar duplicata
    foreach ($teamHolidays as $h) {
        if ($h['date'] === $date) errorResponse('Feriado já cadastrado nesta data');
    }

    $holiday = [
        'id' => generateUUID(),
        'team_id' => $teamId,
        'date' => $date,
        'name' => sanitize($name),
    ];
    $holidays[] = $holiday;
    dataWrite('holidays', $holidays);

    jsonResponse(['ok' => true, 'holiday' => $holiday]);
}

if ($method === 'DELETE') {
    $body = getBody();
    $id = $body['id'] ?? '';

    if (!$id) errorResponse('ID obrigatório');

    $holiday = findById($holidays, $id);
    if (!$holiday || $holiday['team_id'] !== $teamId) errorResponse('Feriado não encontrado', 404);

    $holidays = removeById($holidays, $id);
    dataWrite('holidays', $holidays);

    jsonResponse(['ok' => true]);
}

errorResponse('Método não permitido', 405);
