<?php
/**
 * Funções utilitárias e helpers
 */

// Tradutor de datas para português
function getPortugueseTranslations() {
    return [
        'days' => [
            'Monday'    => 'Segunda-feira',
            'Tuesday'   => 'Terça-feira',
            'Wednesday' => 'Quarta-feira',
            'Thursday'  => 'Quinta-feira',
            'Friday'    => 'Sexta-feira',
            'Saturday'  => 'Sábado',
            'Sunday'    => 'Domingo'
        ],
        'months' => [
            'January'   => 'Janeiro',
            'February'  => 'Fevereiro',
            'March'     => 'Março',
            'April'     => 'Abril',
            'May'       => 'Maio',
            'June'      => 'Junho',
            'July'      => 'Julho',
            'August'    => 'Agosto',
            'September' => 'Setembro',
            'October'   => 'Outubro',
            'November'  => 'Novembro',
            'December'  => 'Dezembro'
        ]
    ];
}

function getPortugueseDate($date) {
    $translations = getPortugueseTranslations();
    
    $diaSemanaIngles = $date->format('l');
    $mesIngles = $date->format('F');
    
    $diaSemana = $translations['days'][$diaSemanaIngles] ?? $diaSemanaIngles;
    $mes = $translations['months'][$mesIngles] ?? $mesIngles;
    
    return [
        'dayName' => $diaSemana,
        'monthName' => $mes
    ];
}

function initializeConfig($configFile) {
    if (file_exists($configFile)) {
        return json_decode(file_get_contents($configFile), true);
    } else {
        $config = [
            'team' => ["Felaço", "Aline", "Mikke", "Jonathan", "Debarba", "Guilherme", "Ederson", "Priscilla", "Gustavo", "Marcelo", "Lucas", "Joao"],
            'startDate' => '2025-05-07',
            'startPerson' => 'Mikke'
        ];
        file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT));
        return $config;
    }
}

function getOrderedTeam($config, $currentPresenter) {
    $orderedTeam = [];
    $currentIndex = array_search($currentPresenter, $config['team']);
    
    // Adiciona o atual e os próximos
    for ($i = $currentIndex; $i < count($config['team']); $i++) {
        $orderedTeam[] = $config['team'][$i];
    }
    
    // Adiciona os anteriores (que já apresentaram)
    for ($i = 0; $i < $currentIndex; $i++) {
        $orderedTeam[] = $config['team'][$i];
    }
    
    return $orderedTeam;
}

function getPresentersContext($config, $currentPresenter, $holidays, $vacations) {
    // Validações iniciais
    if (empty($config['team']) || !is_array($config['team'])) {
        return [
            'previous' => 'N/A',
            'current' => $currentPresenter ?? 'N/A',
            'next' => 'N/A'
        ];
    }
    
    $today = new DateTime();
    $currentIndex = array_search($currentPresenter, $config['team']);
    
    // Se o apresentador atual não for encontrado, usar o primeiro da lista
    if ($currentIndex === false) {
        $currentIndex = 0;
        $currentPresenter = $config['team'][0];
    }
    
    // Encontra o próximo apresentador disponível (não está de férias)
    $nextIndex = ($currentIndex + 1) % count($config['team']);
    $nextMember = $config['team'][$nextIndex] ?? 'N/A';
    
    // Verifica se o próximo membro está de férias
    if ($nextMember !== 'N/A') {
        $nextDate = getNextBusinessDay($today, $holidays);
        $attempts = 0;
        while (isOnVacation($nextMember, $nextDate, $vacations) && $attempts < count($config['team'])) {
            $nextIndex = ($nextIndex + 1) % count($config['team']);
            $nextMember = $config['team'][$nextIndex] ?? 'N/A';
            $attempts++;
        }
    }
    
    // Encontra o apresentador anterior disponível (não está de férias)
    $previousIndex = ($currentIndex - 1 + count($config['team'])) % count($config['team']);
    $previousMember = $config['team'][$previousIndex] ?? 'N/A';
    
    // Verifica se o membro anterior está de férias
    if ($previousMember !== 'N/A') {
        $prevDate = getPreviousBusinessDay($today, $holidays);
        $attempts = 0;
        while (isOnVacation($previousMember, $prevDate, $vacations) && $attempts < count($config['team'])) {
            $previousIndex = ($previousIndex - 1 + count($config['team'])) % count($config['team']);
            $previousMember = $config['team'][$previousIndex] ?? 'N/A';
            $attempts++;
        }
    }
    
    return [
        'previous' => $previousMember,
        'current' => $currentPresenter,
        'next' => $nextMember
    ];
}
