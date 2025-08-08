<?php
/**
 * Funções para gerenciamento de férias
 */

function ensureVacationsFile($vacationsFile) {
    if (!file_exists($vacationsFile)) {
        $vacations = []; // Array vazio para armazenar as férias
        file_put_contents($vacationsFile, json_encode($vacations, JSON_PRETTY_PRINT));
        return $vacations;
    } else {
        return json_decode(file_get_contents($vacationsFile), true);
    }
}

function isOnVacation($member, $date, $vacations) {
    if (!isset($vacations[$member])) {
        return false;
    }
    
    $dateStr = $date->format('Y-m-d');
    
    foreach ($vacations[$member] as $vacation) {
        $startDate = new DateTime($vacation['start']);
        $endDate = new DateTime($vacation['end']);
        
        if ($date >= $startDate && $date <= $endDate) {
            return true;
        }
    }
    
    return false;
}

function getNextAvailableMember($team, $currentIndex, $date, $vacations) {
    $index = $currentIndex;
    $loopCount = 0;
    
    do {
        $index = ($index + 1) % count($team);
        $member = $team[$index];
        $loopCount++;
        
        // Evitar loop infinito
        if ($loopCount > count($team)) {
            return $team[$currentIndex]; // Retorna o atual se todos estiverem de férias
        }
    } while (isOnVacation($member, $date, $vacations));
    
    return $member;
}
