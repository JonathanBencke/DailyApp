<?php
session_start();
if (isset($_SESSION['team_id'])) {
    if (!empty($_SESSION['is_admin'])) {
        header('Location: /admin/');
    } else {
        header('Location: /dashboard.php');
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scrum Manager — Login</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body class="login-page">

<div class="login-split">

    <!-- Painel esquerdo: branding -->
    <div class="login-left">
        <div class="login-left-inner">
            <div class="login-brand-icon">
                <svg width="52" height="52" viewBox="0 0 52 52" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <rect width="52" height="52" rx="14" fill="rgba(255,255,255,0.15)"/>
                    <rect x="13" y="10" width="26" height="32" rx="4" fill="rgba(255,255,255,0.9)"/>
                    <rect x="19" y="6" width="5" height="8" rx="2.5" fill="white"/>
                    <rect x="28" y="6" width="5" height="8" rx="2.5" fill="white"/>
                    <rect x="18" y="22" width="16" height="2.5" rx="1.25" fill="rgba(79,70,229,0.7)"/>
                    <rect x="18" y="28" width="12" height="2.5" rx="1.25" fill="rgba(79,70,229,0.4)"/>
                    <rect x="18" y="34" width="8" height="2.5" rx="1.25" fill="rgba(79,70,229,0.25)"/>
                </svg>
            </div>
            <h1 class="login-brand-name">Scrum Manager</h1>
            <p class="login-brand-desc">Organize as apresentações da daily do seu time de forma simples e automática.</p>

            <ul class="login-features">
                <li>
                    <span class="feat-icon">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M3 8l3.5 3.5L13 4.5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    Rotação automática entre membros
                </li>
                <li>
                    <span class="feat-icon">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M3 8l3.5 3.5L13 4.5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    Gestão de ausências e feriados
                </li>
                <li>
                    <span class="feat-icon">
                        <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M3 8l3.5 3.5L13 4.5" stroke="white" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                    Login individual por time
                </li>
            </ul>
        </div>

        <!-- Círculos decorativos -->
        <div class="deco-circle deco-c1"></div>
        <div class="deco-circle deco-c2"></div>
        <div class="deco-circle deco-c3"></div>
    </div>

    <!-- Painel direito: formulário -->
    <div class="login-right">
        <div class="login-form-wrap">
            <!-- Logo mobile (só aparece em telas pequenas) -->
            <div class="login-mobile-brand">
                <svg width="36" height="36" viewBox="0 0 52 52" fill="none"><rect width="52" height="52" rx="14" fill="var(--primary)"/><rect x="13" y="10" width="26" height="32" rx="4" fill="rgba(255,255,255,0.9)"/><rect x="19" y="6" width="5" height="8" rx="2.5" fill="white"/><rect x="28" y="6" width="5" height="8" rx="2.5" fill="white"/><rect x="18" y="22" width="16" height="2.5" rx="1.25" fill="rgba(79,70,229,0.7)"/><rect x="18" y="28" width="12" height="2.5" rx="1.25" fill="rgba(79,70,229,0.4)"/><rect x="18" y="34" width="8" height="2.5" rx="1.25" fill="rgba(79,70,229,0.25)"/></svg>
                <span>Scrum Manager</span>
            </div>

            <div class="login-form-header">
                <h2>Bem-vindo de volta</h2>
                <p>Entre com as credenciais do seu time</p>
            </div>

            <form id="login-form" autocomplete="off">
                <div class="login-field">
                    <label for="team">Time</label>
                    <div class="input-icon-wrap">
                        <span class="input-icon">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm-5 6a5 5 0 0 1 10 0H3z" fill="#9ca3af"/></svg>
                        </span>
                        <input type="text" id="team" name="team" placeholder="Nome do time" required>
                    </div>
                </div>

                <div class="login-field">
                    <label for="password">Senha</label>
                    <div class="input-icon-wrap">
                        <span class="input-icon">
                            <svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M11 7V5a3 3 0 1 0-6 0v2H4a1 1 0 0 0-1 1v5a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V8a1 1 0 0 0-1-1h-1zm-5 0V5a2 2 0 1 1 4 0v2H6z" fill="#9ca3af"/></svg>
                        </span>
                        <input type="password" id="password" name="password" placeholder="Senha do time" required>
                    </div>
                </div>

                <div id="login-error" class="login-error-msg" style="display:none"></div>

                <button type="submit" class="login-btn" id="btn-login">
                    <span class="login-btn-text">Entrar</span>
                    <span class="login-btn-icon">
                        <svg width="18" height="18" viewBox="0 0 18 18" fill="none"><path d="M3 9h12M10 4l5 5-5 5" stroke="white" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
                    </span>
                </button>
            </form>
        </div>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script>
$(function() {
    $('#login-form').on('submit', function(e) {
        e.preventDefault();
        var btn = $('#btn-login');
        btn.addClass('loading').prop('disabled', true);
        $('#login-error').hide();
        $.ajax({
            url: '/api/auth.php',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                team: $('#team').val().trim(),
                password: $('#password').val()
            }),
            success: function(res) {
                btn.addClass('success');
                window.location.href = res.redirect || '/dashboard.php';
            },
            error: function(xhr) {
                var msg = (xhr.responseJSON && xhr.responseJSON.error) ? xhr.responseJSON.error : 'Time ou senha incorretos';
                $('#login-error').text(msg).show();
                btn.removeClass('loading').prop('disabled', false);
                $('#password').val('').focus();
            }
        });
    });
});
</script>
</body>
</html>
