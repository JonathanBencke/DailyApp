<?php
define('IS_API', true);
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/data.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireLogin();
$teamId = getTeamId();
$method = getMethod();
$absences = dataRead('absences');
$teamAbsences = filterBy($absences, 'team_id', $teamId);

if ($method === 'GET') {
    $members = dataRead('members');
    $result = array_map(function ($a) use ($members) {
        $member = findById($members, $a['member_id']);
        $a['member_name'] = $member ? $member['name'] : 'Desconhecido';
        return $a;
    }, $teamAbsences);
    usort($result, fn($a, $b) => strcmp($a['start_date'], $b['start_date']));
    jsonResponse($result);
}

if ($method === 'POST') {
    $body = getBody();
    $memberId = $body['member_id'] ?? '';
    $startDate = $body['start_date'] ?? '';
    $endDate = $body['end_date'] ?? '';

    if (!$memberId || !$startDate || !$endDate) errorResponse('Campos obrigatórios: member_id, start_date, end_date');
    if (!isValidDate($startDate) || !isValidDate($endDate)) errorResponse('Datas inválidas (use YYYY-MM-DD)');
    if ($startDate > $endDate) errorResponse('Data inicial não pode ser maior que a final');

    $members = dataRead('members');
    $member = findById($members, $memberId);
    if (!$member || $member['team_id'] !== $teamId) errorResponse('Membro não encontrado', 404);

    $absence = [
        'id' => generateUUID(),
        'team_id' => $teamId,
        'member_id' => $memberId,
        'start_date' => $startDate,
        'end_date' => $endDate,
    ];
    $absences[] = $absence;
    dataWrite('absences', $absences);

    jsonResponse(['ok' => true, 'absence' => $absence]);
}

if ($method === 'DELETE') {
    $body = getBody();
    $id = $body['id'] ?? '';

    if (!$id) errorResponse('ID obrigatório');

    $absence = findById($absences, $id);
    if (!$absence || $absence['team_id'] !== $teamId) errorResponse('Ausência não encontrada', 404);

    $absences = removeById($absences, $id);
    dataWrite('absences', $absences);

    jsonResponse(['ok' => true]);
}

errorResponse('Método não permitido', 405);
