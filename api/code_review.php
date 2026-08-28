<?php
define('IS_API', true);
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/data.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireLogin();
$teamId = getTeamId();
$method = getMethod();

function crGetActiveMembers($teamId) {
    $members = dataRead('members');
    $active = array_filter($members, fn($m) => $m['team_id'] === $teamId && $m['active']);
    usort($active, fn($a, $b) => $a['order'] - $b['order']);
    return array_values($active);
}

function crGetMemberAbsent($teamId, $memberId, $date) {
    $absences = dataRead('absences');
    foreach ($absences as $a) {
        if ($a['team_id'] === $teamId && $a['member_id'] === $memberId) {
            if ($date >= $a['start_date'] && $date <= $a['end_date']) return true;
        }
    }
    return false;
}

if ($method === 'GET') {
    $reviews = dataRead('code_review');
    $teamReviews = array_filter($reviews, fn($r) => $r['team_id'] === $teamId);
    // Sort by created_at DESC
    usort($teamReviews, fn($a, $b) => strcmp($b['created_at'], $a['created_at']));
    $teamReviews = array_values(array_slice($teamReviews, 0, 20));
    jsonResponse($teamReviews);
}

if ($method === 'POST') {
    $body = getBody();
    $action = $body['action'] ?? '';

    if ($action === 'draw') {
        $prTitle  = trim($body['pr_title'] ?? '');
        $authorId = trim($body['author_id'] ?? '');
        $today    = date('Y-m-d');

        $members  = crGetActiveMembers($teamId);
        if (!$members) {
            jsonError('Nenhum membro ativo no time', 400);
        }

        // Resolve author name
        $authorName = '';
        foreach ($members as $m) {
            if ($m['id'] === $authorId) { $authorName = $m['name']; break; }
        }

        // Filter eligible: exclude author, exclude absent today
        $eligible = array_values(array_filter($members, function ($m) use ($teamId, $authorId, $today) {
            if ($m['id'] === $authorId) return false;
            if (crGetMemberAbsent($teamId, $m['id'], $today)) return false;
            return true;
        }));

        if (!$eligible) {
            jsonError('Nenhum membro elegível para revisão (verifique ausências)', 400);
        }

        $reviewer = $eligible[array_rand($eligible)];

        $review = [
            'id'            => generateUUID(),
            'team_id'       => $teamId,
            'pr_title'      => $prTitle,
            'author_id'     => $authorId,
            'author_name'   => $authorName,
            'reviewer_id'   => $reviewer['id'],
            'reviewer_name' => $reviewer['name'],
            'date'          => $today,
            'created_at'    => date('Y-m-d\TH:i:s'),
        ];

        $reviews   = dataRead('code_review');
        $reviews[] = $review;
        dataWrite('code_review', $reviews);

        jsonResponse(['ok' => true, 'reviewer' => $reviewer, 'review' => $review]);
    }

    jsonError('Ação inválida', 400);
}

jsonError('Método não permitido', 405);
