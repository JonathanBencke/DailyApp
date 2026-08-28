<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/layout.php';
requireLogin();
pageHeader('Code Review', 'code-review');
?>

<div class="page-header">
    <h1>Sorteador de Code Review</h1>
</div>

<div class="card" style="max-width:480px;margin-bottom:24px">
    <div class="form-group">
        <label for="cr-pr-title">PR / Tarefa <span style="color:var(--gray-500);font-weight:400">(opcional)</span></label>
        <input type="text" id="cr-pr-title" class="form-control" placeholder="ex: feature/login, bugfix/auth...">
    </div>
    <div class="form-group">
        <label for="cr-author-select">Autor (não participa do sorteio)</label>
        <select id="cr-author-select" class="form-control">
            <option value="">— Nenhum —</option>
        </select>
    </div>
    <div id="cr-draw-msg" class="alert" style="display:none"></div>
    <button class="btn btn-primary btn-lg" id="btn-draw-cr" style="width:100%">🎲 Sortear Revisor</button>
</div>

<div class="cr-result-card" id="cr-result-card">
    <div class="cr-result-label">Revisor Sorteado</div>
    <div class="cr-result-name" id="cr-result-name"></div>
</div>

<div style="margin-top:32px">
    <h2 class="section-title">Histórico de Sorteios</h2>
    <div class="card" style="padding:0;overflow:hidden">
        <table class="data-table" id="cr-history-table">
            <thead>
                <tr>
                    <th>Data</th>
                    <th>PR / Tarefa</th>
                    <th>Autor</th>
                    <th>Revisor</th>
                </tr>
            </thead>
            <tbody id="cr-history-body">
                <tr><td colspan="4" class="loading">Carregando...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<?php pageFooter(); ?>
