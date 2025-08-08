<div class="config-panel">
    <div class="tabs">
        <div class="tab active" onclick="switchTab('team-tab')">Equipe</div>
        <div class="tab" onclick="switchTab('holidays-tab')">Feriados</div>
        <div class="tab" onclick="switchTab('vacations-tab')">Férias</div>
    </div>
    
    <div id="team-tab" class="tab-content active">
        <h2>Configurações da Equipe</h2>
        
        <form method="post">
            <input type="hidden" name="action" value="update_team">
            <div class="form-group">
                <label for="team">Ordem de apresentadores (separados por vírgula)</label>
                <textarea id="team" name="team"><?= htmlspecialchars(implode(',', $config['team'])) ?></textarea>
            </div>
            <button type="submit">Atualizar Lista</button>
        </form>
        
        <form method="post">
            <input type="hidden" name="action" value="reset_start">
            <div class="form-row">
                <div class="form-group">
                    <label for="startDate">Data da apresentação</label>
                    <input type="date" id="startDate" name="startDate">
                </div>
                <div class="form-group" style="margin-left: 5px;">
                    <label for="startPerson">Pessoa na data</label>
                    <select id="startPerson" name="startPerson">
                        <option value="">-</option>
                        <?php foreach ($config['team'] as $member): ?>
                            <option value="<?= htmlspecialchars($member) ?>">
                                <?= htmlspecialchars($member) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <button type="submit">Definir apresentador</button>
        </form>
    </div>
    
    <div id="holidays-tab" class="tab-content">
        <h2>Gerenciar Feriados</h2>
        
        <form method="post">
            <input type="hidden" name="action" value="add_holiday">
            <div class="form-row">
                <div class="form-group">
                    <label for="holidayDate">Data do feriado</label>
                    <input type="date" id="holidayDate" name="holidayDate" required>
                </div>
                <div class="form-group">
                    <label for="holidayName">Nome do feriado</label>
                    <input type="text" id="holidayName" name="holidayName" required>
                </div>
            </div>
            <button type="submit">Adicionar Feriado</button>
        </form>
        
        <div class="holidays-list">
            <?php foreach ($sortedHolidays as $date => $name): ?>
                <div class="holiday-item">
                    <span class="holiday-date"><?= (new DateTime($date))->format('d/m/Y') ?></span>
                    <span class="holiday-name"><?= htmlspecialchars($name) ?></span>
                    <form method="post" style="display: inline;">
                        <input type="hidden" name="action" value="remove_holiday">
                        <input type="hidden" name="holidayToRemove" value="<?= htmlspecialchars($date) ?>">
                        <button type="submit" class="remove-btn">X</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
    
    <div id="vacations-tab" class="tab-content">
        <h2>Gerenciar Férias da Equipe</h2>
        
        <form method="post">
            <input type="hidden" name="action" value="add_vacation">
            <div class="form-row">
                <div class="form-group">
                    <label for="vacationMember">Membro da equipe</label>
                    <select id="vacationMember" name="vacationMember" required>
                        <option value="">Selecione...</option>
                        <?php foreach ($config['team'] as $member): ?>
                            <option value="<?= htmlspecialchars($member) ?>"><?= htmlspecialchars($member) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="vacationStart">Data de início</label>
                    <input type="date" id="vacationStart" name="vacationStart" required>
                </div>
                <div class="form-group">
                    <label for="vacationEnd">Data de fim</label>
                    <input type="date" id="vacationEnd" name="vacationEnd" required>
                </div>
            </div>
            <button type="submit">Registrar Férias</button>
        </form>
        
        <div class="vacations-list">
            <?php if (empty($sortedVacations)): ?>
                <div class="no-vacations-message">
                    Nenhum período de férias registrado.
                </div>
            <?php else: ?>
                <?php foreach ($sortedVacations as $member => $periods): ?>
                    <div style="margin-bottom: 10px; border-bottom: 1px solid #ddd; padding-bottom: 5px;">
                        <h3 style="margin: 5px 0; font-size: 16px;"><?= htmlspecialchars($member) ?></h3>
                        <?php foreach ($periods as $index => $period): ?>
                            <div class="vacation-item">
                                <span class="vacation-period">
                                    <?= (new DateTime($period['start']))->format('d/m/Y') ?>
                                    até
                                    <?= (new DateTime($period['end']))->format('d/m/Y') ?>
                                </span>
                                <form method="post" style="display: inline;">
                                    <input type="hidden" name="action" value="remove_vacation">
                                    <input type="hidden" name="vacationMember" value="<?= htmlspecialchars($member) ?>">
                                    <input type="hidden" name="vacationIndex" value="<?= $index ?>">
                                    <button type="submit" class="remove-btn">X</button>
                                </form>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>
