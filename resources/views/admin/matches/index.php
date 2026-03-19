<?php declare(strict_types=1);
/** @var App\Domain\Competition\Competition $competition */
/** @var list<array<string, mixed>> $matches */
/** @var list<App\Domain\Competition\MatchGroup> $groups */
/** @var list<App\Domain\Competition\MatchVenue> $venues */
/** @var list<array{id: int, entity_type: string, display_name: string, short_code: ?string}> $entities */
/** @var App\Support\Sessions\SessionManager $session */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Wedstrijden: <?= htmlspecialchars($competition->name, ENT_QUOTES, 'UTF-8') ?></h1>
    <a href="/admin/competitions/<?= $competition->id ?>/edit" class="btn btn-secondary">← Terug</a>
</div>

<div class="card mb-4">
    <div class="card-header">Nieuwe wedstrijd toevoegen</div>
    <div class="card-body">
        <form method="POST" action="/admin/competitions/<?= $competition->id ?>/matches">
            <input type="hidden" name="<?= htmlspecialchars($session->get('csrf_token_name', '_token'), ENT_QUOTES, 'UTF-8') ?>"
                   value="<?= htmlspecialchars($session->get('csrf_token', ''), ENT_QUOTES, 'UTF-8') ?>">
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Thuis *</label>
                    <select class="form-select" name="home_entity_id" required>
                        <option value="">Kies...</option>
                        <?php foreach ($entities as $e): ?>
                            <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['display_name'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Uit *</label>
                    <select class="form-select" name="away_entity_id" required>
                        <option value="">Kies...</option>
                        <?php foreach ($entities as $e): ?>
                            <option value="<?= $e['id'] ?>"><?= htmlspecialchars($e['display_name'], ENT_QUOTES, 'UTF-8') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">Fase</label>
                    <input type="text" class="form-control" name="stage" value="group" placeholder="group/knockout">
                </div>
                <div class="col-md-2 mb-3">
                    <label class="form-label">Aftrap *</label>
                    <input type="datetime-local" class="form-control" name="kickoff_at" required>
                </div>
                <div class="col-md-1 mb-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary">+</button>
                </div>
            </div>
        </form>
    </div>
</div>

<table class="table table-striped table-sm">
    <thead>
        <tr>
            <th>Thuis</th>
            <th>Uit</th>
            <th>Aftrap</th>
            <th>Fase</th>
            <th>Groep</th>
            <th>Acties</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($matches as $m): ?>
            <tr>
                <td><?= htmlspecialchars(is_scalar($m['home_label'] ?? null) ? (string)$m['home_label'] : '', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars(is_scalar($m['away_label'] ?? null) ? (string)$m['away_label'] : '', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars(is_scalar($m['kickoff_at'] ?? null) ? (string)$m['kickoff_at'] : '', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars(is_scalar($m['stage'] ?? null) ? (string)$m['stage'] : '', ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars(is_scalar($m['group_name'] ?? null) ? (string)$m['group_name'] : '-', ENT_QUOTES, 'UTF-8') ?></td>
                <td class="d-flex gap-1">
                    <a href="/admin/competitions/<?= $competition->id ?>/matches/<?= (int)($m['id'] ?? 0) ?>/edit" class="btn btn-sm btn-outline-primary">Bewerken</a>
                    <a href="/admin/competitions/<?= $competition->id ?>/results/<?= (int)($m['id'] ?? 0) ?>/edit" class="btn btn-sm btn-outline-success">Uitslag</a>
                    <form method="POST" action="/admin/competitions/<?= $competition->id ?>/matches/<?= (int)($m['id'] ?? 0) ?>/delete" class="d-inline">
                        <input type="hidden" name="<?= htmlspecialchars($session->get('csrf_token_name', '_token'), ENT_QUOTES, 'UTF-8') ?>"
                               value="<?= htmlspecialchars($session->get('csrf_token', ''), ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                onclick="return confirm('Wedstrijd verwijderen?')">✕</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
