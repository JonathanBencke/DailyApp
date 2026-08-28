<?php
define('IS_API', true);
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/data.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireLogin();
$teamId = getTeamId();
$method = getMethod();

// GET — return current rotation config
if ($method === 'GET') {
    $state    = dataReadMap('presenter_state');
    $rotation = $state[$teamId] ?? [];
    jsonResponse([
        'anchor_date'      => $rotation['anchor_date']       ?? '',
        'anchor_member_id' => $rotation['anchor_member_id']  ?? '',
    ]);
}

if ($method === 'POST') {
    $body   = getBody();
    $action = $body['action'] ?? 'password';

    // Save rotation anchor
    if ($action === 'rotation') {
        $anchorDate     = $body['anchor_date']       ?? '';
        $anchorMemberId = $body['anchor_member_id']  ?? '';

        if (!$anchorDate || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $anchorDate)) {
            errorResponse('Data inválida');
        }
        if (!$anchorMemberId) errorResponse('Membro inválido');

        // Verify member belongs to this team
        $members     = dataRead('members');
        $memberFound = false;
        foreach ($members as $m) {
            if ($m['id'] === $anchorMemberId && $m['team_id'] === $teamId) {
                $memberFound = true; break;
            }
        }
        if (!$memberFound) errorResponse('Membro não encontrado');

        $state = dataReadMap('presenter_state');
        $state[$teamId] = [
            'anchor_date'      => $anchorDate,
            'anchor_member_id' => $anchorMemberId,
            'day_skips'        => [], // reset per-day skips when rotation is reconfigured
        ];
        dataWriteMap('presenter_state', $state);
        jsonResponse(['ok' => true]);
    }

    // Change password (default action)
    $currentPassword = $body['current_password'] ?? '';
    $newPassword     = $body['new_password']     ?? '';

    if (!$currentPassword || !$newPassword) errorResponse('Campos obrigatórios');
    if (strlen($newPassword) < 4) errorResponse('Nova senha deve ter ao menos 4 caracteres');

    $teams = dataRead('teams');
    $team  = findById($teams, $teamId);
    if (!$team) errorResponse('Time não encontrado', 404);

    if (!password_verify($currentPassword, $team['password'])) {
        errorResponse('Senha atual incorreta', 401);
    }

    $teams = updateById($teams, $teamId, ['password' => password_hash($newPassword, PASSWORD_BCRYPT)]);
    dataWrite('teams', $teams);

    jsonResponse(['ok' => true]);
}

errorResponse('Método não permitido', 405);
