<?php
require_once __DIR__ . '/../includes/auth_check.php';
require_once __DIR__ . '/../includes/data.php';
require_once __DIR__ . '/../includes/layout.php';
requireAdmin();
pageHeader('Administração', 'admin');
?>

<div class="page-header">
    <h2 class="page-title">Administração de Times</h2>
</div>

<div class="card">
    <h3>Criar Novo Time</h3>
    <div class="form-row">
        <div class="form-group">
            <label>Nome do Time</label>
            <input type="text" id="new-team-name" class="form-control" placeholder="Ex: Equipe Alpha">
        </div>
        <div class="form-group">
            <label>Senha</label>
            <input type="password" id="new-team-password" class="form-control" placeholder="Mín. 4 caracteres">
        </div>
        <div class="form-group form-group-btn">
            <button class="btn btn-primary" id="btn-create-team">Criar Time</button>
        </div>
    </div>
    <div id="team-form-error" class="alert" style="display:none"></div>
</div>

<div class="card">
    <h3>Times Cadastrados</h3>
    <table class="data-table" id="teams-table">
        <thead>
            <tr>
                <th>Nome</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody id="teams-body">
            <tr><td colspan="2" class="loading">Carregando...</td></tr>
        </tbody>
    </table>
</div>

<!-- Modal: Trocar senha do time -->
<div id="modal-pwd" style="display:none;position:fixed;inset:0;background:rgba(0,0,0,.4);z-index:200;align-items:center;justify-content:center">
    <div class="card" style="max-width:360px;width:100%;margin:0">
        <h3>Redefinir Senha</h3>
        <p id="modal-team-name" style="margin-bottom:12px;color:#6b7280"></p>
        <div class="form-group">
            <label>Nova Senha</label>
            <input type="password" id="modal-new-pwd" class="form-control" placeholder="Mín. 4 caracteres">
        </div>
        <div id="modal-error" class="alert alert-error" style="display:none"></div>
        <div style="display:flex;gap:10px;margin-top:12px">
            <button class="btn btn-primary" id="btn-modal-save">Salvar</button>
            <button class="btn btn-secondary" id="btn-modal-cancel">Cancelar</button>
        </div>
    </div>
</div>

<script>
var PAGE = 'admin';
var editingTeamId = null;

$(function () {
    function showAlert(sel, msg, type) {
        $(sel).removeClass('alert-error alert-success').addClass('alert-' + (type || 'error')).text(msg).show();
    }
    function hideAlert(sel) { $(sel).hide(); }

    function loadTeams() {
        $.get('/api/teams.php', function (teams) {
            if (!teams.length) {
                $('#teams-body').html('<tr><td colspan="2" class="loading">Nenhum time cadastrado.</td></tr>');
                return;
            }
            var rows = teams.map(function (t) {
                return '<tr>'
                    + '<td><strong>' + $('<span>').text(t.name).html() + '</strong></td>'
                    + '<td style="display:flex;gap:8px">'
                    + '<button class="btn btn-sm btn-secondary btn-edit-team" data-id="' + t.id + '" data-name="' + $('<span>').text(t.name).html() + '">Redefinir Senha</button>'
                    + '<button class="btn btn-sm btn-danger btn-del-team" data-id="' + t.id + '" data-name="' + $('<span>').text(t.name).html() + '">Excluir</button>'
                    + '</td></tr>';
            }).join('');
            $('#teams-body').html(rows);
        });
    }

    loadTeams();

    $('#btn-create-team').on('click', function () {
        hideAlert('#team-form-error');
        var name = $('#new-team-name').val().trim();
        var pwd = $('#new-team-password').val();
        if (!name) { showAlert('#team-form-error', 'Informe o nome do time'); return; }
        if (pwd.length < 4) { showAlert('#team-form-error', 'Senha deve ter ao menos 4 caracteres'); return; }
        var btn = $(this).prop('disabled', true);
        $.ajax({ url: '/api/teams.php', method: 'POST', contentType: 'application/json', data: JSON.stringify({ name: name, password: pwd }) })
            .done(function () {
                showAlert('#team-form-error', 'Time criado com sucesso!', 'success');
                $('#new-team-name').val('');
                $('#new-team-password').val('');
                loadTeams();
            })
            .fail(function (xhr) { showAlert('#team-form-error', (xhr.responseJSON && xhr.responseJSON.error) || 'Erro'); })
            .always(function () { btn.prop('disabled', false); });
    });

    // Redefinir senha
    $(document).on('click', '.btn-edit-team', function () {
        editingTeamId = $(this).data('id');
        $('#modal-team-name').text('Time: ' + $(this).data('name'));
        $('#modal-new-pwd').val('');
        hideAlert('#modal-error');
        $('#modal-pwd').css('display', 'flex');
        setTimeout(function () { $('#modal-new-pwd').focus(); }, 50);
    });

    $('#btn-modal-cancel').on('click', function () { $('#modal-pwd').hide(); });

    $('#btn-modal-save').on('click', function () {
        hideAlert('#modal-error');
        var pwd = $('#modal-new-pwd').val();
        if (pwd.length < 4) { showAlert('#modal-error', 'Senha deve ter ao menos 4 caracteres'); return; }
        var btn = $(this).prop('disabled', true);
        $.ajax({ url: '/api/teams.php', method: 'PUT', contentType: 'application/json', data: JSON.stringify({ id: editingTeamId, password: pwd }) })
            .done(function () { $('#modal-pwd').hide(); })
            .fail(function (xhr) { showAlert('#modal-error', (xhr.responseJSON && xhr.responseJSON.error) || 'Erro'); })
            .always(function () { btn.prop('disabled', false); });
    });

    // Excluir time
    $(document).on('click', '.btn-del-team', function () {
        var id = $(this).data('id');
        var name = $(this).data('name');
        if (!confirm('Excluir o time "' + name + '"?\nTodos os membros, ausências e feriados serão removidos.')) return;
        $.ajax({ url: '/api/teams.php', method: 'DELETE', contentType: 'application/json', data: JSON.stringify({ id: id }) })
            .done(function () { loadTeams(); });
    });

    // Fechar modal clicando fora
    $('#modal-pwd').on('click', function (e) {
        if ($(e.target).is('#modal-pwd')) $(this).hide();
    });
});
</script>

<?php pageFooter(); ?>
