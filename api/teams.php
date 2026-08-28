<?php
define('IS_API', true);
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/data.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireAdmin();

$method = getMethod();
$teams = dataRead('teams');

if ($method === 'GET') {
    $safe = array_map(fn($t) => ['id' => $t['id'], 'name' => $t['name']], $teams);
    jsonResponse($safe);
}

if ($method === 'POST') {
    $body = getBody();
    $name = trim($body['name'] ?? '');
    $password = $body['password'] ?? '';

    if (!$name) errorResponse('Nome do time é obrigatório');
    if (strlen($password) < 4) errorResponse('Senha deve ter ao menos 4 caracteres');

    foreach ($teams as $t) {
        if (strtolower($t['name']) === strtolower($name)) {
            errorResponse('Time com este nome já existe');
        }
    }

    $team = [
        'id' => generateUUID(),
        'name' => sanitize($name),
        'password' => password_hash($password, PASSWORD_BCRYPT),
    ];
    $teams[] = $team;
    dataWrite('teams', $teams);

    jsonResponse(['ok' => true, 'team' => ['id' => $team['id'], 'name' => $team['name']]]);
}

if ($method === 'PUT') {
    $body = getBody();
    $id = $body['id'] ?? '';
    $password = $body['password'] ?? '';

    if (!$id) errorResponse('ID obrigatório');
    if (strlen($password) < 4) errorResponse('Senha deve ter ao menos 4 caracteres');

    $team = findById($teams, $id);
    if (!$team) errorResponse('Time não encontrado', 404);

    $teams = updateById($teams, $id, ['password' => password_hash($password, PASSWORD_BCRYPT)]);
    dataWrite('teams', $teams);

    jsonResponse(['ok' => true]);
}

if ($method === 'DELETE') {
    $body = getBody();
    $id = $body['id'] ?? '';

    if (!$id) errorResponse('ID obrigatório');

    $teams = removeById($teams, $id);
    dataWrite('teams', $teams);

    // Remove membros do time
    $members = dataRead('members');
    $members = array_values(array_filter($members, fn($m) => $m['team_id'] !== $id));
    dataWrite('members', $members);

    // Remove ausências e feriados
    $absences = dataRead('absences');
    $absences = array_values(array_filter($absences, fn($a) => $a['team_id'] !== $id));
    dataWrite('absences', $absences);

    $holidays = dataRead('holidays');
    $holidays = array_values(array_filter($holidays, fn($h) => $h['team_id'] !== $id));
    dataWrite('holidays', $holidays);

    jsonResponse(['ok' => true]);
}

errorResponse('Método não permitido', 405);
