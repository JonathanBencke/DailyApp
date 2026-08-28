<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/layout.php';
requireLogin();
pageHeader('Membros', 'members');
?>

<div class="page-header">
    <h2 class="page-title">Membros do Time</h2>
    <button class="btn btn-primary" id="btn-add-member">+ Adicionar Membro</button>
</div>

<div id="add-member-form" class="card" style="display:none">
    <h3>Novo Membro</h3>
    <div class="form-row">
        <input type="text" id="new-member-name" placeholder="Nome do membro" class="form-control">
        <button class="btn btn-success" id="btn-save-member">Salvar</button>
        <button class="btn btn-secondary" id="btn-cancel-member">Cancelar</button>
    </div>
    <div id="member-form-error" class="alert alert-error" style="display:none"></div>
</div>

<div class="card">
    <p class="hint">Arraste para reordenar. A ordem define quem apresenta primeiro.</p>
    <ul class="sortable-list" id="members-list">
        <li class="loading">Carregando...</li>
    </ul>
</div>

<?php pageFooter(); ?>
