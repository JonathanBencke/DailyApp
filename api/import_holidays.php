<?php
define('IS_API', true);
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/data.php';
require_once __DIR__ . '/../includes/auth_check.php';

requireLogin();
$teamId = getTeamId();
$method = getMethod();

// GET ?year=2026 — busca feriados nacionais na BrasilAPI
if ($method === 'GET') {
    $year = intval($_GET['year'] ?? date('Y'));
    if ($year < 2000 || $year > 2100) errorResponse('Ano inválido');

    $url = "https://brasilapi.com.br/api/feriados/v1/{$year}";

    $ctx = stream_context_create([
        'http' => [
            'timeout'        => 10,
            'ignore_errors'  => true,
            'header'         => "User-Agent: DailyManager/1.0\r\nAccept: application/json\r\n",
        ],
        'ssl' => [
            'verify_peer'       => false,
            'verify_peer_name'  => false,
        ],
    ]);

    $raw = @file_get_contents($url, false, $ctx);

    if ($raw === false) {
        // Tenta fallback com cURL
        if (function_exists('curl_init')) {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT        => 10,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_USERAGENT      => 'DailyManager/1.0',
            ]);
            $raw = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            if (!$raw || $httpCode >= 400) {
                errorResponse('Não foi possível conectar à API de feriados. Verifique a conexão do servidor.');
            }
        } else {
            errorResponse('Servidor sem acesso à internet ou sem suporte a requisições externas.');
        }
    }

    $data = json_decode($raw, true);
    if (!is_array($data)) {
        errorResponse('Resposta inválida da API de feriados.');
    }

    // Buscar feriados já cadastrados para este time neste ano
    $existing = dataRead('holidays');
    $existingDates = [];
    foreach ($existing as $h) {
        if ($h['team_id'] === $teamId && substr($h['date'], 0, 4) === (string)$year) {
            $existingDates[] = $h['date'];
        }
    }

    // Montar lista de retorno
    $holidays = [];
    foreach ($data as $item) {
        if (empty($item['date']) || empty($item['name'])) continue;
        $holidays[] = [
            'date'     => $item['date'],
            'name'     => $item['name'],
            'type'     => $item['type'] ?? 'national',
            'imported' => in_array($item['date'], $existingDates),
        ];
    }

    usort($holidays, fn($a, $b) => strcmp($a['date'], $b['date']));
    jsonResponse(['year' => $year, 'holidays' => $holidays]);
}

// POST — importar feriados selecionados em lote
if ($method === 'POST') {
    $body = getBody();
    $items = $body['holidays'] ?? [];

    if (!is_array($items) || empty($items)) {
        errorResponse('Nenhum feriado selecionado');
    }

    $existing = dataRead('holidays');
    $existingDates = [];
    foreach ($existing as $h) {
        if ($h['team_id'] === $teamId) $existingDates[] = $h['date'];
    }

    $added = 0;
    $skipped = 0;
    foreach ($items as $item) {
        $date = $item['date'] ?? '';
        $name = trim($item['name'] ?? '');
        if (!$date || !$name || !isValidDate($date)) { $skipped++; continue; }
        if (in_array($date, $existingDates)) { $skipped++; continue; }

        $existing[] = [
            'id'      => generateUUID(),
            'team_id' => $teamId,
            'date'    => $date,
            'name'    => sanitize($name),
        ];
        $existingDates[] = $date;
        $added++;
    }

    if ($added > 0) dataWrite('holidays', $existing);

    jsonResponse(['ok' => true, 'added' => $added, 'skipped' => $skipped]);
}

errorResponse('Método não permitido', 405);
