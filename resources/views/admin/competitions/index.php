<?php declare(strict_types=1);
/** @var App\Support\View\ViewRenderer $renderer */
/** @var list<App\Domain\Competition\Competition> $competitions */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Competities</h1>
    <a href="/admin/competitions/create" class="btn btn-primary">Nieuwe competitie</a>
</div>

<?php if ($competitions === []): ?>
    <div class="alert alert-info">Er zijn nog geen competities aangemaakt.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Naam</th>
                    <th>Slug</th>
                    <th>Status</th>
                    <th>Deadline</th>
                    <th>Openbaar</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($competitions as $c): ?>
                    <tr>
                        <td><?= $renderer->e($c->name) ?></td>
                        <td><code><?= $renderer->e($c->slug) ?></code></td>
                        <td>
                            <?php $statusClass = match($c->status->value) {
                                'open' => 'success',
                                'active' => 'primary',
                                'draft' => 'secondary',
                                'closed' => 'warning',
                                'archived' => 'dark',
                                default => 'secondary',
                            }; ?>
                            <span class="badge text-bg-<?= $statusClass ?>"><?= $renderer->e($c->status->value) ?></span>
                        </td>
                        <td><?= $renderer->e($c->submissionDeadline) ?></td>
                        <td><?= $c->isPublic ? '✓' : '–' ?></td>
                        <td class="text-end">
                            <div class="btn-group btn-group-sm">
                                <a href="/admin/competitions/<?= $c->id ?>/edit" class="btn btn-outline-primary">Bewerken</a>
                                <a href="/admin/competitions/<?= $c->id ?>/participants" class="btn btn-outline-secondary">Deelnemers</a>
                                <a href="/admin/competitions/<?= $c->id ?>/matches" class="btn btn-outline-secondary">Wedstrijden</a>
                                <a href="/admin/competitions/<?= $c->id ?>/bonus-questions" class="btn btn-outline-secondary">Bonusvragen</a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
