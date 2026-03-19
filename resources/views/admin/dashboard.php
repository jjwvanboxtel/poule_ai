<?php declare(strict_types=1);
/** @var App\Support\View\ViewRenderer $renderer */
/** @var int $totalCompetitions */
/** @var int $totalUsers */
/** @var list<App\Domain\Competition\Competition> $competitions */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Admin Dashboard</h1>
</div>

<div class="row mb-4">
    <div class="col-md-3">
        <div class="card text-white bg-primary">
            <div class="card-body">
                <h5 class="card-title">Competities</h5>
                <p class="card-text display-6"><?= $totalCompetitions ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card text-white bg-success">
            <div class="card-body">
                <h5 class="card-title">Gebruikers</h5>
                <p class="card-text display-6"><?= $totalUsers ?></p>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <h2>Snelle navigatie</h2>
        <div class="list-group list-group-horizontal-md mb-4">
            <a href="/admin/competitions" class="list-group-item list-group-item-action">🏆 Competities</a>
            <a href="/admin/users" class="list-group-item list-group-item-action">👥 Gebruikers</a>
            <a href="/admin/maintenance" class="list-group-item list-group-item-action">🔧 Onderhoud</a>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <h2>Recente competities</h2>
        <table class="table table-striped table-hover">
            <thead>
                <tr>
                    <th>Naam</th>
                    <th>Status</th>
                    <th>Start</th>
                    <th>Eind</th>
                    <th>Acties</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($competitions as $competition): ?>
                    <tr>
                        <td><?= htmlspecialchars($competition->name, ENT_QUOTES, 'UTF-8') ?></td>
                        <td><span class="badge bg-secondary"><?= htmlspecialchars($competition->status->value, ENT_QUOTES, 'UTF-8') ?></span></td>
                        <td><?= htmlspecialchars($competition->startDate, ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($competition->endDate, ENT_QUOTES, 'UTF-8') ?></td>
                        <td>
                            <a href="/admin/competitions/<?= $competition->id ?>/edit" class="btn btn-sm btn-outline-primary">Bewerken</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
