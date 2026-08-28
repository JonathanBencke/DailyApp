<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/layout.php';
requireLogin();
pageHeader('Configurações', 'settings');
?>

<div class="page-header">
    <h2 class="page-title">Configurações do Time</h2>
</div>

<div class="card" style="max-width:420px">
    <h3>Alterar Senha</h3>
    <div class="form-group">
        <label>Senha Atual</label>
        <input type="password" id="current-password" class="form-control" placeholder="Senha atual">
    </div>
    <div class="form-group">
        <label>Nova Senha</label>
        <input type="password" id="new-password" class="form-control" placeholder="Nova senha (mín. 4 caracteres)">
    </div>
    <div class="form-group">
        <label>Confirmar Nova Senha</label>
        <input type="password" id="confirm-password" class="form-control" placeholder="Confirme a nova senha">
    </div>
    <div id="settings-msg" class="alert" style="display:none"></div>
    <button class="btn btn-primary" id="btn-save-password">Salvar Nova Senha</button>
</div>

<div class="card" style="max-width:420px;margin-top:24px">
    <h3>Rotação da Daily</h3>
    <p style="font-size:.85rem;color:var(--gray-400);margin-bottom:16px">
        Define a partir de quando a rotação começa e quem apresenta primeiro, conforme a ordem de apresentação.
    </p>
    <div class="form-group">
        <label>Data de início</label>
        <input type="date" id="rotation-date" class="form-control">
    </div>
    <div class="form-group">
        <label>Quem apresenta nessa data</label>
        <select id="rotation-member" class="form-control">
            <option value="">Carregando membros...</option>
        </select>
    </div>
    <div id="rotation-msg" class="alert" style="display:none"></div>
    <button class="btn btn-primary" id="btn-save-rotation">Salvar Rotação</button>
</div>

<?php pageFooter(); ?>
