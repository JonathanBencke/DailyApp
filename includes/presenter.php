<?php
/**
 * Funções para cálculo de apresentadores
 */

function getCurrentPresenter($config, $holidays, $vacations) {
    // Validações iniciais
    if (empty($config['team']) || !is_array($config['team'])) {
        return null;
    }
    
    $startDate = new DateTime($config['startDate']);
    $today = new DateTime();
    $today->setTime(0, 0, 0);
    $startIndex = array_search($config['startPerson'], $config['team']);
    
    // Se a pessoa inicial não for encontrada na equipe, usar o primeiro membro
    if ($startIndex === false) {
        $startIndex = 0;
        $config['startPerson'] = $config['team'][0];
    }
    
    // Se data de início for posterior à data atual, retorna o apresentador inicial
    if ($startDate > $today) {
        return $config['startPerson'];
    }
    
    // Calcula dias úteis entre as datas
    $currentIndex = $startIndex;
    $currentDate = clone $startDate;
    $currentDate->setTime(0, 0, 0);
    
    while ($currentDate < $today) {
        if (isBusinessDay($currentDate, $holidays)) {
            // Avança para o próximo membro independente de férias
            // A lógica de férias é tratada no final
            $currentIndex = ($currentIndex + 1) % count($config['team']);
        }
        $currentDate->modify('+1 day');
    }
    
    // Verifica se o membro atual está de férias hoje
    $currentMember = $config['team'][$currentIndex];
    if (isOnVacation($currentMember, $today, $vacations)) {
        return getNextAvailableMember($config['team'], $currentIndex, $today, $vacations);
    }
    
    return $currentMember;
}

function getPresentationDates($config, $currentPresenter, $holidays, $vacations) {
    $dates = [];
    $today = new DateTime();
    $currentIndex = array_search($currentPresenter, $config['team']);
    
    // Garantir que o currentIndex seja válido
    if ($currentIndex === false) {
        $currentIndex = 0;
        $currentPresenter = $config['team'][0];
    }
    
    // O apresentador atual tem a data de hoje
    $dates[$currentPresenter] = clone $today;
    
    // Calcula datas para os próximos apresentadores
    $nextDate = clone $today;
    $processedMembers = [$currentPresenter];
    
    // Processar todos os outros membros da equipe
    $remainingMembers = array_diff($config['team'], $processedMembers);
    
    foreach ($remainingMembers as $member) {
        // Avança para o próximo dia útil
        $nextDate = getNextBusinessDay($nextDate, $holidays);
        
        // Se o membro estiver de férias nesta data, procura a próxima data disponível
        $attempts = 0;
        while (isOnVacation($member, $nextDate, $vacations) && $attempts < 365) {
            $nextDate = getNextBusinessDay($nextDate, $holidays);
            $attempts++;
        }
        
        $dates[$member] = clone $nextDate;
        $processedMembers[] = $member;
    }
    
    // Verificar se todos os membros da equipe têm uma data
    foreach ($config['team'] as $member) {
        if (!isset($dates[$member])) {
            // Se por algum motivo um membro não tem data, atribui uma data futura
            $fallbackDate = clone $today;
            $fallbackDate->modify('+' . (count($dates) + 1) . ' days');
            $dates[$member] = getNextBusinessDay($fallbackDate, $holidays);
        }
    }
    
    return $dates;
}
