<?php declare(strict_types=1);
/** @var App\Support\View\ViewRenderer $renderer */
/** @var App\Support\Sessions\SessionManager $session */
/** @var App\Domain\Competition\Competition $competition */
/** @var array<string, mixed> $match */
/** @var list<array{id: int, entity_type: string, display_name: string, short_code: string|null}> $entities */
?>
<div class="mb-4">
    <a href="/admin/competitions/<?= $competition->id ?>/matches" class="btn btn-sm btn-outline-secondary">&larr; Terug</a>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-dark text-white">
        <h1 class="h5 mb-0">Wedstrijd bewerken</h1>
    </div>
    <div class="card-body">
        <form method="POST" action="/admin/competitions/<?= $competition->id ?>/matches/<?= (int) $match['id'] ?>/edit">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($session->get('csrf_token', ''), ENT_QUOTES, 'UTF-8') ?>">

            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Thuisploeg</label>
                    <select name="home_entity_id" class="form-select" required>
                        <?php foreach ($entities as $e): ?>
                            <option value="<?= $e['id'] ?>" <?= (int) ($match['home_entity_id'] ?? 0) === $e['id'] ? 'selected' : '' ?>>
                                <?= $renderer->e($e['display_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Uitploeg</label>
                    <select name="away_entity_id" class="form-select" required>
                        <?php foreach ($entities as $e): ?>
                            <option value="<?= $e['id'] ?>" <?= (int) ($match['away_entity_id'] ?? 0) === $e['id'] ? 'selected' : '' ?>>
                                <?= $renderer->e($e['display_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Fase</label>
                    <select name="stage" class="form-select">
                        <?php foreach (['group', 'round_of_16', 'quarter_final', 'semi_final', 'final', 'other'] as $stage): ?>
                            <option value="<?= $stage ?>" <?= ($match['stage'] ?? '') === $stage ? 'selected' : '' ?>>
                                <?= $renderer->e($stage) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Aftrap</label>
                    <input type="datetime-local" name="kickoff_at" class="form-control"
                           value="<?= $renderer->e(str_replace(' ', 'T', (string) ($match['kickoff_at'] ?? ''))) ?>" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Status</label>
                    <select name="status" class="form-select">
                        <?php foreach (['scheduled', 'in_progress', 'completed', 'cancelled'] as $st): ?>
                            <option value="<?= $st ?>" <?= ($match['status'] ?? '') === $st ? 'selected' : '' ?>>
                                <?= $renderer->e($st) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Opslaan</button>
                <a href="/admin/competitions/<?= $competition->id ?>/matches" class="btn btn-link">Annuleren</a>
            </div>
        </form>
    </div>
</div>
