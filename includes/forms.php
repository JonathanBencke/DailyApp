<?php
/**
 * Funções para processamento de formulários
 */

function processFormSubmissions($config, $holidays, $vacations, $configFile, $holidaysFile, $vacationsFile) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'update_team':
                $config['team'] = array_map('trim', explode(',', $_POST['team']));
                $config['team'] = array_filter($config['team']); // Remove entradas vazias
                file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT));
                break;
            
            case 'reset_start':
                $config['startDate'] = $_POST['startDate'];
                $config['startPerson'] = $_POST['startPerson'];
                file_put_contents($configFile, json_encode($config, JSON_PRETTY_PRINT));
                break;
                
            case 'add_holiday':
                $newHolidayDate = $_POST['holidayDate'];
                $newHolidayName = trim($_POST['holidayName']);
                
                if (!empty($newHolidayDate) && !empty($newHolidayName)) {
                    $holidays[$newHolidayDate] = $newHolidayName;
                    file_put_contents($holidaysFile, json_encode($holidays, JSON_PRETTY_PRINT));
                }
                break;
                
            case 'remove_holiday':
                $holidayToRemove = $_POST['holidayToRemove'];
                
                if (isset($holidays[$holidayToRemove])) {
                    unset($holidays[$holidayToRemove]);
                    file_put_contents($holidaysFile, json_encode($holidays, JSON_PRETTY_PRINT));
                }
                break;
                
            case 'add_vacation':
                $member = $_POST['vacationMember'];
                $startDate = $_POST['vacationStart'];
                $endDate = $_POST['vacationEnd'];
                
                if (!empty($member) && !empty($startDate) && !empty($endDate)) {
                    if (!isset($vacations[$member])) {
                        $vacations[$member] = [];
                    }
                    
                    $vacations[$member][] = [
                        'start' => $startDate,
                        'end' => $endDate
                    ];
                    
                    file_put_contents($vacationsFile, json_encode($vacations, JSON_PRETTY_PRINT));
                }
                break;
                
            case 'remove_vacation':
                $member = $_POST['vacationMember'];
                $index = $_POST['vacationIndex'];
                
                if (isset($vacations[$member]) && isset($vacations[$member][$index])) {
                    array_splice($vacations[$member], $index, 1);
                    
                    // Se não há mais férias para esse membro, remova a entrada
                    if (empty($vacations[$member])) {
                        unset($vacations[$member]);
                    }
                    
                    file_put_contents($vacationsFile, json_encode($vacations, JSON_PRETTY_PRINT));
                }
                break;
        }
        
        // Recarrega dados após processamento
        $config = json_decode(file_get_contents($configFile), true);
        $holidays = json_decode(file_get_contents($holidaysFile), true);
        $vacations = json_decode(file_get_contents($vacationsFile), true);
    }
    
    return [$config, $holidays, $vacations];
}
