<?php
/**
 * Stateless endpoint for integrations (n8n, webhooks, etc.)
 * POST /api/daily.php
 * Body: {"team": "name", "password": "pass"}
 * Returns today's presenter without requiring a session.
 */
define('IS_API', true);
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/data.php';

if (getMethod() !== 'POST') {
    errorResponse('Método não permitido', 405);
}

$body     = getBody();
$teamName = trim($body['team'] ?? '');
$password = $body['password'] ?? '';

if (!$teamName || !$password) {
    errorResponse('team e password são obrigatórios');
}

// Authenticate
$teams = dataRead('teams');
$team  = null;
foreach ($teams as $t) {
    if (strtolower($t['name']) === strtolower($teamName)) {
        $team = $t;
        break;
    }
}

if (!$team || !password_verify($password, $team['password'])) {
    errorResponse('Time ou senha incorretos', 401);
}

$teamId = $team['id'];
$today  = date('Y-m-d');

// ── Helpers (duplicated from presenter.php to keep this endpoint self-contained) ──

function isWorkDay($teamId, $date) {
    $workdays     = dataReadMap('workdays');
    $teamWorkdays = $workdays[$teamId] ?? ['days' => [1, 2, 3, 4, 5]];
    $dayOfWeek    = (int)date('N', strtotime($date));
    return in_array($dayOfWeek, $teamWorkdays['days']);
}

function isHoliday($teamId, $date) {
    $holidays = dataRead('holidays');
    foreach ($holidays as $h) {
        if ($h['team_id'] === $teamId && $h['date'] === $date) return true;
    }
    return false;
}

function getMemberAbsent($teamId, $memberId, $date) {
    $absences = dataRead('absences');
    foreach ($absences as $a) {
        if ($a['team_id'] === $teamId && $a['member_id'] === $memberId) {
            if ($date >= $a['start_date'] && $date <= $a['end_date']) return true;
        }
    }
    return false;
}

function getActiveMembers($teamId) {
    $members = dataRead('members');
    $active  = array_filter($members, fn($m) => $m['team_id'] === $teamId && $m['active']);
    usort($active, fn($a, $b) => $a['order'] - $b['order']);
    return array_values($active);
}

function skipAbsentMembers($teamId, $members, $n, $startIndex, $date) {
    for ($i = 0; $i < $n; $i++) {
        $idx = ($startIndex + $i) % $n;
        if (!getMemberAbsent($teamId, $members[$idx]['id'], $date)) {
            return $idx;
        }
    }
    return -1;
}

// Simulates the rotation forward from the anchor date to $targetDate, consuming
// absent members' slots (an absent member loses their turn). MUST stay in sync
// with the copy in presenter.php.
function resolveRotationIndex($teamId, $members, $n, $anchorDate, $anchorIndex, $targetDate, $daySkips) {
    if ($targetDate < $anchorDate) return null;

    $resolvedIndex = $anchorIndex;
    $cursor        = $anchorDate;
    $isAnchorDay   = true;

    while (true) {
        if (isWorkDay($teamId, $cursor) && !isHoliday($teamId, $cursor)) {
            if ($isAnchorDay) {
                $resolvedIndex = ($anchorIndex + (int)($daySkips[$cursor] ?? 0)) % $n;
                $isAnchorDay   = false;
            } else {
                $resolvedIndex = ($resolvedIndex + 1 + (int)($daySkips[$cursor] ?? 0)) % $n;
            }
            $found = skipAbsentMembers($teamId, $members, $n, $resolvedIndex, $cursor);
            if ($found >= 0) {
                $resolvedIndex = $found;
            }

            if ($cursor === $targetDate) {
                return $found >= 0 ? $resolvedIndex : null;
            }
        }
        if ($cursor >= $targetDate) break;
        $cursor = date('Y-m-d', strtotime($cursor . ' +1 day'));
    }

    return null;
}

function getPresenterForDate($teamId, $date) {
    if (!isWorkDay($teamId, $date) || isHoliday($teamId, $date)) return null;

    $members = getActiveMembers($teamId);
    $n       = count($members);
    if ($n === 0) return null;

    $state          = dataReadMap('presenter_state');
    $teamState      = $state[$teamId] ?? [];
    $anchorDate     = $teamState['anchor_date']      ?? '';
    $anchorMemberId = $teamState['anchor_member_id'] ?? '';

    if (!$anchorDate || $date < $anchorDate) return null;

    $anchorIndex = 0;
    foreach ($members as $i => $m) {
        if ($m['id'] === $anchorMemberId) { $anchorIndex = $i; break; }
    }

    $daySkips = $teamState['day_skips'] ?? [];
    $idx      = resolveRotationIndex($teamId, $members, $n, $anchorDate, $anchorIndex, $date, $daySkips);
    if ($idx === null) return null;
    return $members[$idx];
}

// ── Response ──

$presenter = getPresenterForDate($teamId, $today);
$isWorkday = isWorkDay($teamId, $today);
$holiday   = isHoliday($teamId, $today);

jsonResponse([
    'today'      => $today,
    'is_workday' => $isWorkday,
    'is_holiday' => $holiday,
    'team'       => $team['name'],
    'presenter'  => $presenter ? ['id' => $presenter['id'], 'name' => $presenter['name']] : null,
]);
