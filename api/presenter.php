<?php
define('IS_API', true);
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/data.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireLogin();
$teamId = getTeamId();
$method = getMethod();

function isWorkDay($teamId, $date) {
    $workdays = dataReadMap('workdays');
    $teamWorkdays = $workdays[$teamId] ?? ['days' => [1, 2, 3, 4, 5]];
    $dayOfWeek = (int)date('N', strtotime($date));
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
    $active = array_filter($members, fn($m) => $m['team_id'] === $teamId && $m['active']);
    usort($active, fn($a, $b) => $a['order'] - $b['order']);
    return array_values($active);
}

function getRotationState($teamId) {
    $state = dataReadMap('presenter_state');
    return $state[$teamId] ?? [];
}

// Advances $startIndex past absent members on $date. Returns the index of the
// first non-absent member, or -1 when every member is absent on $date.
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
// absent members' slots (an absent member loses their turn, so no one presents
// twice in a row because of a block of absences). Returns the resolved member
// index for $targetDate, or null when the date is before the anchor or every
// member is absent on that day. $daySkips is a per-date map of extra advances.
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
            // When all members are absent, leave $resolvedIndex at the base so the
            // slot is forfeit and the next workday advances from there.

            if ($cursor === $targetDate) {
                return $found >= 0 ? $resolvedIndex : null;
            }
        }
        if ($cursor >= $targetDate) break;
        $cursor = date('Y-m-d', strtotime($cursor . ' +1 day'));
    }

    return null;
}

// Returns the presenter for a given date using anchor-based automatic rotation.
// Returns null when: not configured, not a workday, holiday, all members absent.
function getPresenterForDate($teamId, $date) {
    if (!isWorkDay($teamId, $date) || isHoliday($teamId, $date)) return null;

    $members = getActiveMembers($teamId);
    $n = count($members);
    if ($n === 0) return null;

    $teamState      = getRotationState($teamId);
    $anchorDate     = $teamState['anchor_date']      ?? '';
    $anchorMemberId = $teamState['anchor_member_id'] ?? '';

    if (!$anchorDate) return null; // rotation not configured yet
    if ($date < $anchorDate) return null; // before rotation start

    // Resolve anchor_member_id to current index (handles re-ordering)
    $anchorIndex = 0;
    foreach ($members as $i => $m) {
        if ($m['id'] === $anchorMemberId) { $anchorIndex = $i; break; }
    }

    $daySkips = $teamState['day_skips'] ?? [];
    $idx = resolveRotationIndex($teamId, $members, $n, $anchorDate, $anchorIndex, $date, $daySkips);
    if ($idx === null) return null;
    return $members[$idx];
}

function getNextPresenters($teamId, $fromDate, $count = 5) {
    $result   = [];
    $date     = $fromDate;
    $attempts = 0;
    while (count($result) < $count && $attempts < 60) {
        $attempts++;
        $date = date('Y-m-d', strtotime($date . ' +1 day'));
        $presenter = getPresenterForDate($teamId, $date);
        if ($presenter !== null) {
            $result[] = ['date' => $date, 'member' => $presenter];
        }
    }
    return $result;
}

function logHistory($teamId, $memberId, $memberName, $date, $histAction) {
    $history   = dataRead('history');
    $history[] = [
        'id'          => generateUUID(),
        'team_id'     => $teamId,
        'member_id'   => $memberId,
        'member_name' => $memberName,
        'date'        => $date,
        'action'      => $histAction,
    ];
    dataWrite('history', $history);
}

function alreadyLoggedToday($teamId, $date, $histAction) {
    $history = dataRead('history');
    foreach ($history as $h) {
        if ($h['team_id'] === $teamId && $h['date'] === $date && $h['action'] === $histAction) {
            return true;
        }
    }
    return false;
}

if ($method === 'GET') {
    $today      = date('Y-m-d');
    $presenter  = getPresenterForDate($teamId, $today);
    $isWorkday  = isWorkDay($teamId, $today);
    $holiday    = isHoliday($teamId, $today);
    $next       = getNextPresenters($teamId, $today, 5);
    $teamState  = getRotationState($teamId);
    $configured = !empty($teamState['anchor_date']);

    // Auto-log the presenter on the first daily load (once per day per team)
    if ($presenter && $configured && !alreadyLoggedToday($teamId, $today, 'presented')) {
        logHistory($teamId, $presenter['id'], $presenter['name'], $today, 'presented');
    }

    jsonResponse([
        'today'           => $today,
        'is_workday'      => $isWorkday,
        'is_holiday'      => $holiday,
        'configured'      => $configured,
        'presenter'       => $presenter,
        'next_presenters' => $next,
    ]);
}

if ($method === 'POST') {
    $body   = getBody();
    $action = $body['action'] ?? '';
    $today  = date('Y-m-d');

    // Swap — advances the rotation past today's presenter permanently.
    // The next available member presents today; tomorrow continues from there.
    if ($action === 'swap') {
        $members = getActiveMembers($teamId);
        $n = count($members);
        if ($n < 2) errorResponse('Precisa de pelo menos 2 membros ativos');

        // Capture current presenter before advancing so we can log the skip
        $skippedPresenter = getPresenterForDate($teamId, $today);
        if (!$skippedPresenter) errorResponse('Não há apresentador para trocar hoje');

        // Advance one slot from today's actual presenter and resolve same-day
        // absences to find who really takes over today.
        $todayIndex = 0;
        foreach ($members as $i => $m) {
            if ($m['id'] === $skippedPresenter['id']) { $todayIndex = $i; break; }
        }
        $nextIndex = ($todayIndex + 1) % $n;
        $found     = skipAbsentMembers($teamId, $members, $n, $nextIndex, $today);
        if ($found < 0) errorResponse('Nenhum membro disponível para assumir a daily hoje');

        // Re-anchor on today with the new presenter so the rotation continues cleanly
        $state    = dataReadMap('presenter_state');
        $state[$teamId]['anchor_date']      = $today;
        $state[$teamId]['anchor_member_id'] = $members[$found]['id'];
        $state[$teamId]['day_skips']        = [];
        dataWriteMap('presenter_state', $state);

        // Remove any 'presented' entry logged for today (auto-log on GET) and
        // replace it with a 'skipped' record for the outgoing presenter.
        $history = dataRead('history');
        $history = array_values(array_filter($history, fn($h) =>
            !($h['team_id'] === $teamId && $h['date'] === $today && $h['action'] === 'presented' && $h['member_id'] === $skippedPresenter['id'])
        ));
        dataWrite('history', $history);
        logHistory($teamId, $skippedPresenter['id'], $skippedPresenter['name'], $today, 'skipped');

        $newPresenter = getPresenterForDate($teamId, $today);
        jsonResponse(['ok' => true, 'presenter' => $newPresenter]);
    }

    // Reset rotation state (admin utility)
    if ($action === 'reset') {
        $state = dataReadMap('presenter_state');
        unset($state[$teamId]);
        dataWriteMap('presenter_state', $state);
        jsonResponse(['ok' => true]);
    }

    errorResponse('Ação inválida');
}

errorResponse('Método não permitido', 405);
