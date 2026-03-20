<?php declare(strict_types=1);
/** @var App\Support\View\ViewRenderer $renderer */
/** @var App\Support\Sessions\SessionManager $session */
/** @var App\Domain\Competition\Competition $competition */
/** @var list<array{id: int, home_entity_id: int, away_entity_id: int, home_label: string, away_label: string, stage: string, kickoff_at: string}> $matches */
?>
<div class="mb-4">
    <a href="/admin/competitions/<?= $competition->id ?>/edit" class="btn btn-sm btn-outline-secondary">&larr; Terug</a>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Wedstrijden: <?= $renderer->e($competition->name) ?></h1>
    <a href="/admin/competitions/<?= $competition->id ?>/matches/create" class="btn btn-primary">Nieuwe wedstrijd</a>
</div>

<?php if ($matches === []): ?>
    <div class="alert alert-info">Nog geen wedstrijden aangemaakt.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Thuis</th>
                    <th>Uit</th>
                    <th>Fase</th>
                    <th>Aftrap</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($matches as $m): ?>
                    <tr>
                        <td><?= $renderer->e($m['home_label']) ?></td>
                        <td><?= $renderer->e($m['away_label']) ?></td>
                        <td><?= $renderer->e($m['stage']) ?></td>
                        <td><?= $renderer->e($m['kickoff_at']) ?></td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="/admin/competitions/<?= $competition->id ?>/matches/<?= $m['id'] ?>/edit"
                                   class="btn btn-outline-primary">Bewerken</a>
                                <a href="/admin/competitions/<?= $competition->id ?>/results/<?= $m['id'] ?>/edit"
                                   class="btn btn-outline-secondary">Uitslag</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
