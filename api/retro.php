<?php
define('IS_API', true);
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/data.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireLogin();
$teamId = getTeamId();
$method = getMethod();

// ===== GET =====
if ($method === 'GET') {
    $type      = $_GET['type'] ?? '';
    $sessionId = $_GET['session_id'] ?? null;

    // List archived sessions
    if ($type === 'sessions') {
        $sessions = array_values(array_filter(dataRead('retro_sessions'), function ($s) use ($teamId) {
            return $s['team_id'] === $teamId;
        }));
        usort($sessions, function ($a, $b) {
            return strcmp($b['date'], $a['date']);
        });

        $allItems = dataRead('retro_items');
        foreach ($sessions as &$s) {
            $s['item_count'] = count(array_filter($allItems, function ($i) use ($s) {
                return ($i['session_id'] ?? null) === $s['id'];
            }));
        }
        unset($s);

        jsonResponse($sessions);
    }

    // View a specific archived session
    if ($sessionId) {
        $sessions = dataRead('retro_sessions');
        $session  = null;
        foreach ($sessions as $s) {
            if ($s['id'] === $sessionId && $s['team_id'] === $teamId) {
                $session = $s;
                break;
            }
        }
        if (!$session) errorResponse('Sessão não encontrada', 404);

        $categories = array_values(array_filter(dataRead('retro_categories'), function ($c) use ($teamId, $sessionId) {
            return $c['team_id'] === $teamId && ($c['session_id'] ?? null) === $sessionId;
        }));
        usort($categories, function ($a, $b) { return ($a['order'] ?? 0) - ($b['order'] ?? 0); });

        $items = array_values(array_filter(dataRead('retro_items'), function ($i) use ($teamId, $sessionId) {
            return $i['team_id'] === $teamId && ($i['session_id'] ?? null) === $sessionId;
        }));

        jsonResponse(['session' => $session, 'categories' => $categories, 'items' => $items]);
    }

    // Active board (session_id = null)
    $categories = array_values(array_filter(dataRead('retro_categories'), function ($c) use ($teamId) {
        return $c['team_id'] === $teamId && ($c['session_id'] ?? null) === null;
    }));
    usort($categories, function ($a, $b) { return ($a['order'] ?? 0) - ($b['order'] ?? 0); });

    $items = array_values(array_filter(dataRead('retro_items'), function ($i) use ($teamId) {
        return $i['team_id'] === $teamId && ($i['session_id'] ?? null) === null;
    }));

    jsonResponse(['categories' => $categories, 'items' => $items]);
}

// ===== POST =====
if ($method === 'POST') {
    $body = getBody();
    $type = $body['type'] ?? '';

    // Archive current board and start new retro session
    if ($type === 'session') {
        $title = trim($body['title'] ?? '');
        $date  = trim($body['date'] ?? date('Y-m-d'));
        if (!$title) errorResponse('Título da retro obrigatório');
        if (!isValidDate($date)) errorResponse('Data inválida');

        $sessionId = generateUUID();

        $sessions   = dataRead('retro_sessions');
        $sessions[] = [
            'id'         => $sessionId,
            'team_id'    => $teamId,
            'title'      => sanitize($title),
            'date'       => $date,
            'created_at' => date('Y-m-d'),
        ];
        dataWrite('retro_sessions', $sessions);

        // Archive active categories → copy as templates for next retro
        $categories    = dataRead('retro_categories');
        $newCategories = [];

        foreach ($categories as &$c) {
            if ($c['team_id'] === $teamId && ($c['session_id'] ?? null) === null) {
                $c['session_id'] = $sessionId;
                $newCategories[] = [
                    'id'         => generateUUID(),
                    'team_id'    => $teamId,
                    'session_id' => null,
                    'name'       => $c['name'],
                    'order'      => $c['order'] ?? 0,
                ];
            }
        }
        unset($c);
        $categories = array_merge($categories, $newCategories);
        dataWrite('retro_categories', $categories);

        // Archive active items
        $items = dataRead('retro_items');
        foreach ($items as &$i) {
            if ($i['team_id'] === $teamId && ($i['session_id'] ?? null) === null) {
                $i['session_id'] = $sessionId;
            }
        }
        unset($i);
        dataWrite('retro_items', $items);

        jsonResponse(['ok' => true, 'session_id' => $sessionId]);
    }

    if ($type === 'category') {
        $name = trim($body['name'] ?? '');
        if (!$name) errorResponse('Nome da categoria obrigatório');

        $categories = dataRead('retro_categories');
        $teamCats   = array_filter($categories, function ($c) use ($teamId) {
            return $c['team_id'] === $teamId && ($c['session_id'] ?? null) === null;
        });
        $maxOrder = 0;
        foreach ($teamCats as $c) {
            if (($c['order'] ?? 0) > $maxOrder) $maxOrder = $c['order'];
        }

        $cat = [
            'id'         => generateUUID(),
            'team_id'    => $teamId,
            'session_id' => null,
            'name'       => sanitize($name),
            'order'      => $maxOrder + 1,
        ];
        $categories[] = $cat;
        dataWrite('retro_categories', $categories);
        jsonResponse(['ok' => true, 'category' => $cat]);
    }

    if ($type === 'item') {
        $categoryId = $body['category_id'] ?? '';
        $text       = trim($body['text'] ?? '');
        $memberId   = $body['member_id'] ?? '';
        $memberName = trim($body['member_name'] ?? '');
        if (!$categoryId || !$text) errorResponse('category_id e text são obrigatórios');

        $categories = dataRead('retro_categories');
        $cat        = null;
        foreach ($categories as $c) {
            if ($c['id'] === $categoryId && $c['team_id'] === $teamId && ($c['session_id'] ?? null) === null) {
                $cat = $c;
                break;
            }
        }
        if (!$cat) errorResponse('Categoria não encontrada', 404);

        $item = [
            'id'          => generateUUID(),
            'team_id'     => $teamId,
            'session_id'  => null,
            'category_id' => $categoryId,
            'text'        => sanitize($text),
            'member_id'   => sanitize($memberId),
            'member_name' => sanitize($memberName),
            'created_at'  => date('Y-m-d'),
        ];
        $items   = dataRead('retro_items');
        $items[] = $item;
        dataWrite('retro_items', $items);
        jsonResponse(['ok' => true, 'item' => $item]);
    }

    errorResponse('type inválido');
}

// ===== PUT =====
if ($method === 'PUT') {
    $body = getBody();
    $type = $body['type'] ?? '';
    $id   = $body['id'] ?? '';

    if ($type === 'category' && $id) {
        $name = trim($body['name'] ?? '');
        if (!$name) errorResponse('Nome obrigatório');

        $categories = dataRead('retro_categories');
        $found      = false;
        foreach ($categories as &$c) {
            if ($c['id'] === $id && $c['team_id'] === $teamId && ($c['session_id'] ?? null) === null) {
                $c['name'] = sanitize($name);
                $found     = true;
                break;
            }
        }
        unset($c);
        if (!$found) errorResponse('Categoria não encontrada', 404);
        dataWrite('retro_categories', $categories);
        jsonResponse(['ok' => true]);
    }

    errorResponse('type ou id inválido');
}

// ===== DELETE =====
if ($method === 'DELETE') {
    $body = getBody();
    $type = $body['type'] ?? '';
    $id   = $body['id'] ?? '';

    if ($type === 'category' && $id) {
        $categories = dataRead('retro_categories');
        $newCats    = array_values(array_filter($categories, function ($c) use ($id, $teamId) {
            return !($c['id'] === $id && $c['team_id'] === $teamId && ($c['session_id'] ?? null) === null);
        }));
        if (count($newCats) === count($categories)) errorResponse('Categoria não encontrada', 404);
        dataWrite('retro_categories', $newCats);

        $items    = dataRead('retro_items');
        $newItems = array_values(array_filter($items, function ($i) use ($id, $teamId) {
            return !($i['category_id'] === $id && $i['team_id'] === $teamId && ($i['session_id'] ?? null) === null);
        }));
        dataWrite('retro_items', $newItems);
        jsonResponse(['ok' => true]);
    }

    if ($type === 'item' && $id) {
        $items    = dataRead('retro_items');
        $newItems = array_values(array_filter($items, function ($i) use ($id, $teamId) {
            return !($i['id'] === $id && $i['team_id'] === $teamId);
        }));
        if (count($newItems) === count($items)) errorResponse('Item não encontrado', 404);
        dataWrite('retro_items', $newItems);
        jsonResponse(['ok' => true]);
    }

    errorResponse('type ou id inválido');
}

errorResponse('Método não permitido', 405);
