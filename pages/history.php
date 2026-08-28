<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/layout.php';
requireLogin();
pageHeader('Histórico', 'history');
?>

<div class="page-header">
    <h2 class="page-title">Histórico de Apresentações</h2>
</div>

<div class="card" style="margin-bottom:20px">
    <div class="cal-nav">
        <button class="btn btn-sm btn-secondary" id="cal-prev">‹</button>
        <span class="cal-month-label" id="cal-month-label"></span>
        <button class="btn btn-sm btn-secondary" id="cal-next">›</button>
    </div>
    <div class="cal-weekdays">
        <div class="cal-weekday">Dom</div>
        <div class="cal-weekday">Seg</div>
        <div class="cal-weekday">Ter</div>
        <div class="cal-weekday">Qua</div>
        <div class="cal-weekday">Qui</div>
        <div class="cal-weekday">Sex</div>
        <div class="cal-weekday">Sáb</div>
    </div>
    <div class="cal-grid" id="cal-grid"></div>
</div>

<div class="card">
    <table class="data-table" id="history-table">
        <thead>
            <tr>
                <th>Data</th>
                <th>Dia</th>
                <th>Membro</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody id="history-body">
            <tr><td colspan="4" class="loading">Carregando...</td></tr>
        </tbody>
    </table>
    <div id="history-empty" style="display:none;text-align:center;color:var(--gray-400);padding:24px 0">
        Nenhuma apresentação registrada ainda.
    </div>
    <div id="history-load-more" style="display:none;text-align:center;margin-top:16px">
        <button class="btn btn-secondary" id="btn-load-more">Carregar mais</button>
    </div>
</div>

<?php pageFooter(); ?>
