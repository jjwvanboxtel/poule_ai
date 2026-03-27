<?php declare(strict_types=1);
/** @var App\Domain\Competition\Competition $competition */
/** @var array<string, mixed> $match */
/** @var list<App\Domain\Competition\MatchGroup> $groups */
/** @var list<App\Domain\Competition\MatchVenue> $venues */
/** @var list<array{id: int, entity_type: string, display_name: string, short_code: ?string}> $entities */
/** @var App\Support\Sessions\SessionManager $session */
$matchId = (int)($match['id'] ?? 0);
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Wedstrijd bewerken</h1>
    <a href="/admin/competitions/<?= $competition->id ?>/matches" class="btn btn-secondary">← Terug</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/admin/competitions/<?= $competition->id ?>/matches/<?= $matchId ?>/edit">
            <input type="hidden" name="<?= htmlspecialchars($session->get('csrf_token_name', '_token'), ENT_QUOTES, 'UTF-8') ?>"
                   value="<?= htmlspecialchars($session->get('csrf_token', ''), ENT_QUOTES, 'UTF-8') ?>">

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Thuis *</label>
                    <select class="form-select" name="home_entity_id" required>
                        <?php foreach ($entities as $e): ?>
                            <option value="<?= $e['id'] ?>" <?= (int)($match['home_entity_id'] ?? 0) === $e['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($e['display_name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Uit *</label>
                    <select class="form-select" name="away_entity_id" required>
                        <?php foreach ($entities as $e): ?>
                            <option value="<?= $e['id'] ?>" <?= (int)($match['away_entity_id'] ?? 0) === $e['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($e['display_name'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Fase</label>
                    <input type="text" class="form-control" name="stage"
                           value="<?= htmlspecialchars(is_scalar($match['stage'] ?? null) ? (string)$match['stage'] : '', ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Aftrap *</label>
                    <input type="datetime-local" class="form-control" name="kickoff_at" required
                           value="<?= htmlspecialchars(str_replace(' ', 'T', is_scalar($match['kickoff_at'] ?? null) ? (string)$match['kickoff_at'] : ''), ENT_QUOTES, 'UTF-8') ?>">
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Groep</label>
                    <select class="form-select" name="group_id">
                        <option value="">Geen groep</option>
                        <?php foreach ($groups as $g): ?>
                            <option value="<?= $g->id ?>" <?= is_numeric($match['group_id'] ?? null) && (int)$match['group_id'] === $g->id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($g->name, ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Opslaan</button>
        </form>
    </div>
</div>
