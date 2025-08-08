<div class="calendar-section">
    <h2>Calendário de Apresentações</h2>
    <div class="calendar-container">
        <table class="calendar-table">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>Dia</th>
                    <th>Apresentador</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // Gerar calendário para os próximos 30 dias úteis
                $calendarDate = clone $today;
                $businessDaysCount = 0;
                $maxDays = 20; // Mostrar próximos 20 dias úteis
                
                // Retrocede ao início da semana atual (segunda-feira)
                $dayOfWeek = (int)$calendarDate->format('N');
                $calendarDate->modify('-' . ($dayOfWeek - 1) . ' days');
                
                for ($i = 0; $i < 60 && $businessDaysCount < $maxDays; $i++) {
                    $dateStr = $calendarDate->format('Y-m-d');
                    $dayTranslation = getPortugueseDate($calendarDate);
                    $dayName = $dayTranslation['dayName'];
                    $isBusinessDayCalendar = isBusinessDay($calendarDate, $holidays);
                    
                    // Encontrar o apresentador para esta data
                    $presenter = null;
                    $onVacation = false;
                    
                    if ($isBusinessDayCalendar) {
                        $businessDaysCount++;
                        
                        foreach ($presentationDates as $member => $date) {
                            if ($date !== null && $date->format('Y-m-d') === $dateStr) {
                                $presenter = $member;
                                break;
                            }
                        }
                        
                        // Se não encontrou o apresentador, mas é dia útil, calcular
                        if (!$presenter) {
                            // Lógica simplificada para determinar o apresentador
                            if ($calendarDate == $today) {
                                $presenter = $currentPresenter;
                            } else if ($calendarDate > $today) {
                                // Implementação simplificada - na prática isso seria calculado corretamente
                                $presenter = "A definir";
                            }
                        }
                        
                        if ($presenter) {
                            $onVacation = isOnVacation($presenter, $calendarDate, $vacations);
                        }
                    }
                    
                    // Define estilo para a linha
                    $rowClass = "";
                    if ($calendarDate->format('Y-m-d') === $today->format('Y-m-d')) {
                        $rowClass = "calendar-today";
                    } else if (!$isBusinessDayCalendar) {
                        $rowClass = "calendar-weekend";
                    }
                    
                    // Só mostra dias úteis ou dias da semana atual
                    $currentWeek = $calendarDate->format('W') === $today->format('W');
                    if ($isBusinessDayCalendar || $currentWeek):
                ?>
                    <tr class="<?= $rowClass ?>">
                        <td><?= $calendarDate->format('d/m/Y') ?></td>
                        <td><?= $dayName ?></td>
                        <td>
                            <?php if ($isBusinessDayCalendar): ?>
                                <?= htmlspecialchars($presenter ?: 'N/A') ?>
                            <?php else: ?>
                                <?php if (isset($holidays[$dateStr])): ?>
                                    Feriado: <?= htmlspecialchars($holidays[$dateStr]) ?>
                                <?php else: ?>
                                    Fim de semana
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($isBusinessDayCalendar): ?>
                                <?php if ($onVacation): ?>
                                    <span class="calendar-status-vacation">Férias</span>
                                <?php else: ?>
                                    <span class="calendar-status-available">Disponível</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <?php if (isset($holidays[$dateStr])): ?>
                                    <span class="calendar-status-holiday">Feriado</span>
                                <?php else: ?>
                                    <span class="calendar-status-no-daily">Sem daily</span>
                                <?php endif; ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php
                    endif;
                    $calendarDate->modify('+1 day');
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
