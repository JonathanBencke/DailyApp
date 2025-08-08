<div class="team-list">
    <h2>Lista de Apresentadores</h2>
    <div class="team-members">
        <?php foreach ($orderedTeam as $member):
            $isOnVacationMember = isset($vacations[$member]) && isOnVacation($member, $presentationDates[$member] ?? $today, $vacations);
            $memberClass = $member === $currentPresenter ? 'current-member' : '';
            $memberClass .= $isOnVacationMember ? ' vacation-member' : '';
        ?>
            <div class="team-member <?= $memberClass ?>">
                <?= (isset($presentationDates[$member]) && $presentationDates[$member] !== null) ? $presentationDates[$member]->format('d/m') : '--' ?> - <?= htmlspecialchars($member) ?>
                <?php if ($isOnVacationMember): ?>
                    <span class="vacation-badge" title="Em férias">F</span>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
    </div>
</div>
