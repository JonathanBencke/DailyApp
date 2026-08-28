<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/layout.php';
requireLogin();
pageHeader('Retro', 'retro');
?>

<div class="page-header">
    <h2 class="page-title">Retro do Time</h2>
    <div style="display:flex;gap:8px">
        <button class="btn btn-secondary" id="btn-new-retro">Encerrar Retro</button>
        <button class="btn btn-primary" id="btn-new-category">+ Nova Categoria</button>
    </div>
</div>

<!-- Form nova categoria -->
<div id="retro-new-category-form" style="display:none;margin-bottom:20px">
    <div class="card" style="max-width:400px">
        <div class="form-group">
            <label class="form-label">Nome da categoria</label>
            <input type="text" class="form-control" id="retro-cat-name" placeholder="Ex: O que foi bom" maxlength="80">
        </div>
        <div style="display:flex;gap:8px">
            <button class="btn btn-primary btn-sm" id="btn-save-category">Salvar</button>
            <button class="btn btn-secondary btn-sm" id="btn-cancel-category">Cancelar</button>
        </div>
    </div>
</div>

<!-- Header da sessão arquivada (read-only) -->
<div id="retro-session-header" class="retro-session-header" style="display:none">
    <div>
        <strong id="retro-session-title"></strong>
        <span class="retro-session-meta" id="retro-session-date" style="margin-left:12px"></span>
    </div>
    <button class="btn btn-sm btn-secondary" id="btn-back-to-active">← Voltar ao board ativo</button>
</div>

<div id="retro-board-empty" style="display:none;text-align:center;color:var(--gray-400);padding:48px 0">
    Nenhuma categoria ainda. Clique em <strong>+ Nova Categoria</strong> para começar.
</div>

<div class="retro-board" id="retro-board">
    <div class="loading">Carregando...</div>
</div>

<!-- Lista de retros anteriores -->
<div class="retro-sessions-section" id="retro-sessions-section" style="display:none">
    <div class="retro-sessions-title">Retros Anteriores</div>
    <div id="retro-sessions-list"></div>
</div>

<!-- Modal: Encerrar Retro -->
<div class="modal-overlay" id="modal-new-retro" style="display:none">
    <div class="modal-box">
        <div class="modal-title">Encerrar Retro atual</div>
        <p style="font-size:.88rem;color:var(--gray-500);margin-bottom:20px">
            O board atual será salvo no histórico e as categorias serão copiadas para a próxima retro (sem itens).
        </p>
        <div class="form-group">
            <label class="form-label">Título da retro</label>
            <input type="text" class="form-control" id="retro-session-title-input" placeholder="Ex: Sprint 23" maxlength="80">
        </div>
        <div class="form-group">
            <label class="form-label">Data</label>
            <input type="date" class="form-control" id="retro-session-date-input">
        </div>
        <div class="modal-actions">
            <button class="btn btn-secondary" id="btn-cancel-new-retro">Cancelar</button>
            <button class="btn btn-primary" id="btn-confirm-new-retro">Encerrar e salvar</button>
        </div>
    </div>
</div>

<?php pageFooter(); ?>
