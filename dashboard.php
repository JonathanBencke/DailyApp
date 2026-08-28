<?php
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/includes/layout.php';
requireLogin();
pageHeader('Hoje', 'dashboard');
?>

<div class="daily-page">
    <header class="daily-hero">
        <div>
            <p class="daily-eyebrow">Daily do time</p>
            <h2 class="page-title">Apresentação de hoje</h2>
        </div>
        <p class="page-date" id="today-date"></p>
    </header>

    <div class="daily-layout">
        <section class="daily-presenter-section" aria-label="Responsável pela apresentação de hoje">
            <div id="daily-status" aria-live="polite">
                <div class="daily-loading">
                    <span class="daily-loading-dot"></span>
                    Carregando apresentação...
                </div>
            </div>
        </section>

        <aside class="weekly-agenda" id="next-presenters-section" style="display:none" aria-labelledby="weekly-agenda-title">
            <div class="weekly-agenda-header">
                <div>
                    <p class="daily-eyebrow">Agenda</p>
                    <h3 class="section-title" id="weekly-agenda-title">Próximas apresentações</h3>
                </div>
            </div>
            <div class="week-strip" id="week-strip"></div>
        </aside>
    </div>
</div>

<?php pageFooter(); ?>
