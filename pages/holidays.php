<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/layout.php';
requireLogin();
pageHeader('Feriados e Dias Úteis', 'holidays');
?>

<div class="page-header">
    <h2 class="page-title">Feriados e Dias Úteis</h2>
</div>

<div class="grid-2">
    <div class="card">
        <h3>Dias Úteis do Time</h3>
        <p class="hint">Selecione os dias da semana em que ocorre a daily.</p>
        <div class="workdays-grid" id="workdays-form">
            <label class="day-check"><input type="checkbox" value="1"> Segunda</label>
            <label class="day-check"><input type="checkbox" value="2"> Terça</label>
            <label class="day-check"><input type="checkbox" value="3"> Quarta</label>
            <label class="day-check"><input type="checkbox" value="4"> Quinta</label>
            <label class="day-check"><input type="checkbox" value="5"> Sexta</label>
            <label class="day-check"><input type="checkbox" value="6"> Sábado</label>
            <label class="day-check"><input type="checkbox" value="7"> Domingo</label>
        </div>
        <button class="btn btn-primary" id="btn-save-workdays" style="margin-top:12px">Salvar Dias Úteis</button>
        <div id="workdays-msg" class="alert" style="display:none"></div>
    </div>

    <div class="card">
        <h3>Adicionar Feriado</h3>
        <div class="form-row">
            <div class="form-group">
                <label>Data</label>
                <input type="date" id="holiday-date" class="form-control">
            </div>
            <div class="form-group">
                <label>Nome</label>
                <input type="text" id="holiday-name" class="form-control" placeholder="Ex: Natal">
            </div>
            <div class="form-group form-group-btn">
                <button class="btn btn-primary" id="btn-add-holiday">Adicionar</button>
            </div>
        </div>
        <div id="holiday-form-error" class="alert alert-error" style="display:none"></div>
    </div>
</div>

<!-- Card: Importar feriados nacionais -->
<div class="card">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:4px">
        <h3 style="margin-bottom:0">Importar Feriados Nacionais</h3>
        <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap">
            <select id="import-year" class="form-control" style="width:110px">
                <?php
                $y = (int)date('Y');
                for ($i = $y - 1; $i <= $y + 2; $i++) {
                    $sel = ($i === $y) ? ' selected' : '';
                    echo "<option value=\"$i\"$sel>$i</option>";
                }
                ?>
            </select>
            <button class="btn btn-secondary" id="btn-fetch-holidays">
                <svg width="15" height="15" viewBox="0 0 15 15" fill="none" style="flex-shrink:0"><path d="M7.5 1v2M7.5 12v2M1 7.5h2M12 7.5h2M3.1 3.1l1.4 1.4M10.5 10.5l1.4 1.4M3.1 11.9l1.4-1.4M10.5 4.5l1.4-1.4" stroke="currentColor" stroke-width="1.4" stroke-linecap="round"/><circle cx="7.5" cy="7.5" r="3" stroke="currentColor" stroke-width="1.4"/></svg>
                Buscar Feriados
            </button>
        </div>
    </div>
    <p class="hint">Busca os feriados nacionais do Brasil via BrasilAPI e permite importá-los com um clique.</p>
    <div id="import-status" style="display:none"></div>
</div>

<!-- Tabela de feriados cadastrados -->
<div class="card">
    <h3>Feriados Cadastrados</h3>
    <table class="data-table" id="holidays-table">
        <thead>
            <tr>
                <th>Data</th>
                <th>Nome</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody id="holidays-body">
            <tr><td colspan="3" class="loading">Carregando...</td></tr>
        </tbody>
    </table>
</div>

<!-- Modal: seleção de feriados para importar -->
<div id="modal-import" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:200;align-items:flex-start;justify-content:center;padding:40px 16px;overflow-y:auto">
    <div class="card" style="max-width:540px;width:100%;margin:0 auto">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
            <h3 style="margin-bottom:0">Feriados Nacionais <span id="modal-import-year"></span></h3>
            <button class="btn btn-secondary btn-sm" id="btn-close-import">✕</button>
        </div>
        <p class="hint" style="margin-bottom:12px">Selecione os feriados que deseja importar. Feriados já cadastrados aparecem marcados e desabilitados.</p>

        <div style="display:flex;gap:8px;margin-bottom:12px">
            <button class="btn btn-secondary btn-sm" id="btn-select-all">Selecionar todos</button>
            <button class="btn btn-secondary btn-sm" id="btn-deselect-all">Desmarcar todos</button>
        </div>

        <div id="import-holiday-list" style="max-height:340px;overflow-y:auto;border:1px solid var(--gray-200);border-radius:8px;margin-bottom:16px"></div>

        <div id="import-modal-error" class="alert alert-error" style="display:none"></div>

        <div style="display:flex;gap:10px;justify-content:flex-end">
            <button class="btn btn-secondary" id="btn-cancel-import">Cancelar</button>
            <button class="btn btn-primary" id="btn-confirm-import">
                <svg width="15" height="15" viewBox="0 0 15 15" fill="none"><path d="M2.5 7.5l3.5 3.5 6.5-6.5" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                Importar Selecionados
            </button>
        </div>
    </div>
</div>

<?php pageFooter(); ?>
