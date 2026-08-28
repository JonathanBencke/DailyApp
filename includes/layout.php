<?php
function pageHeader($title, $activePage = '') {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $teamName = $_SESSION['team_name'] ?? '';
    $isAdmin = $_SESSION['is_admin'] ?? false;
    ?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> — Scrum Manager</title>
    <link rel="stylesheet" href="/css/style.css">
    <script>var PAGE = '<?= htmlspecialchars($activePage) ?>';</script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://code.jquery.com/ui/1.13.2/jquery-ui.min.js"></script>
</head>
<body>
<nav class="navbar">
    <div class="nav-brand">
        <a href="/dashboard.php">📋 Scrum Manager</a>
        <span class="team-badge"><?= htmlspecialchars($teamName) ?></span>
    </div>
    <ul class="nav-links">
        <li><a href="/dashboard.php" <?= $activePage === 'dashboard' ? 'class="active"' : '' ?>>Hoje</a></li>
        <li><a href="/pages/history.php" <?= $activePage === 'history' ? 'class="active"' : '' ?>>Histórico</a></li>
        <li><a href="/pages/retro.php" <?= $activePage === 'retro' ? 'class="active"' : '' ?>>Retro</a></li>
        <li><a href="/pages/code-review.php" <?= $activePage === 'code-review' ? 'class="active"' : '' ?>>Code Review</a></li>
        <li><a href="/pages/members.php" <?= $activePage === 'members' ? 'class="active"' : '' ?>>Membros</a></li>
        <li><a href="/pages/absences.php" <?= $activePage === 'absences' ? 'class="active"' : '' ?>>Ausências</a></li>
        <li><a href="/pages/holidays.php" <?= $activePage === 'holidays' ? 'class="active"' : '' ?>>Feriados</a></li>
        <li><a href="/pages/settings.php" <?= $activePage === 'settings' ? 'class="active"' : '' ?>>Configurações</a></li>
        <?php if ($isAdmin): ?>
        <li><a href="/admin/" <?= $activePage === 'admin' ? 'class="active"' : '' ?>>Admin</a></li>
        <?php endif; ?>
        <li><a href="#" id="btn-logout">Sair</a></li>
    </ul>
    <button class="nav-toggle" id="navToggle">☰</button>
</nav>
<main class="container">
<?php
}

function pageFooter() {
    ?>
</main>
<script src="/js/app.js"></script>
</body>
</html>
<?php
}
