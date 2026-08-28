<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/layout.php';
requireLogin();
pageHeader('Ausências', 'absences');
?>

<div class="page-header">
    <h2 class="page-title">Ausências</h2>
</div>

<div class="card">
    <h3>Registrar Ausência</h3>
    <div class="form-row">
        <div class="form-group">
            <label>Membro</label>
            <select id="absence-member" class="form-control">
                <option value="">Carregando...</option>
            </select>
        </div>
        <div class="form-group">
            <label>Data Início</label>
            <input type="date" id="absence-start" class="form-control">
        </div>
        <div class="form-group">
            <label>Data Fim</label>
            <input type="date" id="absence-end" class="form-control">
        </div>
        <div class="form-group form-group-btn">
            <button class="btn btn-primary" id="btn-save-absence">Registrar</button>
        </div>
    </div>
    <div id="absence-form-error" class="alert alert-error" style="display:none"></div>
</div>

<div class="card">
    <h3>Ausências Cadastradas</h3>
    <table class="data-table" id="absences-table">
        <thead>
            <tr>
                <th>Membro</th>
                <th>Início</th>
                <th>Fim</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody id="absences-body">
            <tr><td colspan="4" class="loading">Carregando...</td></tr>
        </tbody>
    </table>
</div>

<?php pageFooter(); ?>
