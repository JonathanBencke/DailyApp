<?php
/**
 * Funções para gerenciamento de feriados
 */

function ensureHolidaysFile($holidaysFile) {
    if (!file_exists($holidaysFile)) {
        // Feriados fixos e móveis para os próximos anos
        $holidays = [
            // 2025
            '2025-01-01' => 'Confraternização Universal',
            '2025-02-03' => 'Carnaval',
            '2025-02-04' => 'Carnaval',
            '2025-04-18' => 'Sexta-feira Santa',
            '2025-04-20' => 'Páscoa',
            '2025-04-21' => 'Tiradentes',
            '2025-05-01' => 'Dia do Trabalho',
            '2025-06-19' => 'Corpus Christi',
            '2025-09-07' => 'Independência do Brasil',
            '2025-10-12' => 'Nossa Senhora Aparecida',
            '2025-11-02' => 'Finados',
            '2025-11-15' => 'Proclamação da República',
            '2025-12-25' => 'Natal',
            
            // 2026
            '2026-01-01' => 'Confraternização Universal',
            '2026-02-16' => 'Carnaval',
            '2026-02-17' => 'Carnaval',
            '2026-04-03' => 'Sexta-feira Santa',
            '2026-04-05' => 'Páscoa',
            '2026-04-21' => 'Tiradentes',
            '2026-05-01' => 'Dia do Trabalho',
            '2026-06-04' => 'Corpus Christi',
            '2026-09-07' => 'Independência do Brasil',
            '2026-10-12' => 'Nossa Senhora Aparecida',
            '2026-11-02' => 'Finados',
            '2026-11-15' => 'Proclamação da República',
            '2026-12-25' => 'Natal'
        ];
        
        file_put_contents($holidaysFile, json_encode($holidays, JSON_PRETTY_PRINT));
        return $holidays;
    } else {
        return json_decode(file_get_contents($holidaysFile), true);
    }
}

function isHoliday($date, $holidays) {
    $dateStr = $date->format('Y-m-d');
    return isset($holidays[$dateStr]);
}

function isBusinessDay($date, $holidays) {
    $dayOfWeek = $date->format('N');
    return ($dayOfWeek < 6) && !isHoliday($date, $holidays);
}

function getNextBusinessDay($date, $holidays) {
    $nextDay = clone $date;
    do {
        $nextDay->modify('+1 day');
    } while (!isBusinessDay($nextDay, $holidays));
    return $nextDay;
}

function getPreviousBusinessDay($date, $holidays) {
    $prevDay = clone $date;
    do {
        $prevDay->modify('-1 day');
    } while (!isBusinessDay($prevDay, $holidays));
    return $prevDay;
}
