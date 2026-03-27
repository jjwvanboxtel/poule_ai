<?php declare(strict_types=1);
/** @var list<App\Domain\Competition\Competition> $competitions */
/** @var App\Support\Sessions\SessionManager $session */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Competities</h1>
    <a href="/admin/competitions/create" class="btn btn-primary">+ Nieuwe competitie</a>
</div>

<table class="table table-striped table-hover">
    <thead>
        <tr>
            <th>Naam</th>
            <th>Slug</th>
            <th>Status</th>
            <th>Start</th>
            <th>Eind</th>
            <th>Acties</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($competitions as $comp): ?>
            <tr>
                <td><?= htmlspecialchars($comp->name, ENT_QUOTES, 'UTF-8') ?></td>
                <td><code><?= htmlspecialchars($comp->slug, ENT_QUOTES, 'UTF-8') ?></code></td>
                <td><span class="badge bg-secondary"><?= htmlspecialchars($comp->status->value, ENT_QUOTES, 'UTF-8') ?></span></td>
                <td><?= htmlspecialchars($comp->startDate, ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($comp->endDate, ENT_QUOTES, 'UTF-8') ?></td>
                <td class="d-flex gap-1 flex-wrap">
                    <a href="/admin/competitions/<?= $comp->id ?>/edit" class="btn btn-sm btn-outline-primary">Bewerken</a>
                    <a href="/admin/competitions/<?= $comp->id ?>/sections" class="btn btn-sm btn-outline-secondary">Secties</a>
                    <a href="/admin/competitions/<?= $comp->id ?>/matches" class="btn btn-sm btn-outline-secondary">Wedstrijden</a>
                    <a href="/admin/competitions/<?= $comp->id ?>/participants" class="btn btn-sm btn-outline-secondary">Deelnemers</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
