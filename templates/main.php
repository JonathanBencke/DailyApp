<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quem puxa a daily?</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="main-app">
    <div class="container">
        <a href="?logout" class="logout-link">Sair</a>
        <h1>Quem puxa a daily?</h1>
        <div class="date"><?= "$diaSemana, {$dataAtual->format('d')} de $mes de {$dataAtual->format('Y')}" ?></div>
        
        <?php if ($isNotBusinessDay): ?>
            <div class="not-business-day-message">
                <?php if ($isFeriado): ?>
                    Hoje é feriado (<?= htmlspecialchars($feriado) ?>)! Não há daily hoje.
                <?php else: ?>
                    Hoje é fim de semana! Não há daily hoje.
                <?php endif; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($currentOnVacation): ?>
            <div class="vacation-message">
                <?= htmlspecialchars($currentPresenter) ?> está de férias. Substituído pelo próximo disponível.
            </div>
        <?php endif; ?>
        
        <div class="presentation-info">
            <div class="presenter-card previous">
                <div class="label">Dia útil anterior</div>
                <div><?= htmlspecialchars($presenters['previous']) ?></div>
            </div>
            <div class="presenter-card current" style="<?= $isNotBusinessDay ? 'opacity: 0.7;' : '' ?>">
                <div class="label"><?= $isNotBusinessDay ? 'Próxima daily será' : 'Hoje' ?></div>
                <div><?= htmlspecialchars($presenters['current']) ?></div>
            </div>
            <div class="presenter-card next">
                <div class="label">Próximo dia útil</div>
                <div><?= htmlspecialchars($presenters['next']) ?></div>
            </div>
        </div>
        
        <?php include_once 'templates/team_list.php'; ?>
        <?php include_once 'templates/config_panel.php'; ?>
        <?php include_once 'templates/calendar.php'; ?>
    </div>
    
    <script src="assets/js/script.js"></script>
    
    <?php if (isset($_POST['action']) && ($_POST['action'] === 'add_vacation' || $_POST['action'] === 'remove_vacation')): ?>
    <script>
        // Auto-seleciona a aba de férias
        document.addEventListener('DOMContentLoaded', function() {
            const vacationsTab = document.querySelector('.tab[onclick*="vacations-tab"]');
            if (vacationsTab) {
                vacationsTab.click();
            }
        });
    </script>
    <?php endif; ?>
</body>
</html>
