<?php
define('IS_API', true);
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/data.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireLogin();
$teamId = getTeamId();
$method = getMethod();
$members = dataRead('members');
$teamMembers = filterBy($members, 'team_id', $teamId);

if ($method === 'GET') {
    usort($teamMembers, fn($a, $b) => $a['order'] - $b['order']);
    jsonResponse($teamMembers);
}

if ($method === 'POST') {
    $body = getBody();
    $name = trim($body['name'] ?? '');

    if (!$name) errorResponse('Nome é obrigatório');

    $maxOrder = 0;
    foreach ($teamMembers as $m) {
        if ($m['order'] > $maxOrder) $maxOrder = $m['order'];
    }

    $member = [
        'id' => generateUUID(),
        'team_id' => $teamId,
        'name' => sanitize($name),
        'order' => $maxOrder + 1,
        'active' => true,
    ];
    $members[] = $member;
    dataWrite('members', $members);

    jsonResponse(['ok' => true, 'member' => $member]);
}

if ($method === 'PUT') {
    $body = getBody();
    $id = $body['id'] ?? '';

    if (!$id) errorResponse('ID obrigatório');

    $member = findById($members, $id);
    if (!$member || $member['team_id'] !== $teamId) errorResponse('Membro não encontrado', 404);

    $updates = [];

    // Reordenar lista completa
    if (isset($body['order_list']) && is_array($body['order_list'])) {
        $orderList = $body['order_list'];
        $members = array_map(function ($m) use ($orderList, $teamId) {
            if ($m['team_id'] === $teamId) {
                $pos = array_search($m['id'], $orderList);
                if ($pos !== false) {
                    $m['order'] = $pos + 1;
                }
            }
            return $m;
        }, $members);
        dataWrite('members', $members);
        jsonResponse(['ok' => true]);
    }

    if (isset($body['name'])) $updates['name'] = sanitize(trim($body['name']));
    if (isset($body['active'])) $updates['active'] = (bool)$body['active'];

    if ($updates) {
        $members = updateById($members, $id, $updates);
        dataWrite('members', $members);
    }

    jsonResponse(['ok' => true]);
}

if ($method === 'DELETE') {
    $body = getBody();
    $id = $body['id'] ?? '';

    if (!$id) errorResponse('ID obrigatório');

    $member = findById($members, $id);
    if (!$member || $member['team_id'] !== $teamId) errorResponse('Membro não encontrado', 404);

    $members = removeById($members, $id);
    dataWrite('members', $members);

    // Remove ausências do membro
    $absences = dataRead('absences');
    $absences = array_values(array_filter($absences, fn($a) => $a['member_id'] !== $id));
    dataWrite('absences', $absences);

    jsonResponse(['ok' => true]);
}

errorResponse('Método não permitido', 405);
