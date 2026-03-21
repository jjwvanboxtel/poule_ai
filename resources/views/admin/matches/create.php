<?php declare(strict_types=1);
/** @var App\Support\View\ViewRenderer $renderer */
/** @var App\Support\Sessions\SessionManager $session */
/** @var App\Domain\Competition\Competition $competition */
/** @var list<array{id: int, entity_type: string, display_name: string, short_code: string|null}> $entities */
/** @var array<string, string> $errors */
?>
<div class="mb-4">
    <a href="/admin/competitions/<?= $competition->id ?>/matches" class="btn btn-sm btn-outline-secondary">&larr; Terug</a>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-dark text-white">
        <h1 class="h5 mb-0">Nieuwe wedstrijd: <?= $renderer->e($competition->name) ?></h1>
    </div>
    <div class="card-body">
        <form method="POST" action="/admin/competitions/<?= $competition->id ?>/matches">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($session->get('csrf_token', ''), ENT_QUOTES, 'UTF-8') ?>">

            <div class="row g-3">
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Thuisploeg <span class="text-danger">*</span></label>
                    <select name="home_entity_id" class="form-select" required>
                        <option value="">Kies thuisploeg...</option>
                        <?php foreach ($entities as $e): ?>
                            <option value="<?= $e['id'] ?>"><?= $renderer->e($e['display_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label fw-semibold">Uitploeg <span class="text-danger">*</span></label>
                    <select name="away_entity_id" class="form-select" required>
                        <option value="">Kies uitploeg...</option>
                        <?php foreach ($entities as $e): ?>
                            <option value="<?= $e['id'] ?>"><?= $renderer->e($e['display_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fw-semibold">Fase</label>
                    <select name="stage" class="form-select">
                        <option value="group">Groepsfase</option>
                        <option value="round_of_16">Achtste finale</option>
                        <option value="quarter_final">Kwartfinale</option>
                        <option value="semi_final">Halve finale</option>
                        <option value="final">Finale</option>
                        <option value="other">Anders</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Aftrap <span class="text-danger">*</span></label>
                    <input type="datetime-local" name="kickoff_at" class="form-control" required>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Aanmaken</button>
                <a href="/admin/competitions/<?= $competition->id ?>/matches" class="btn btn-link">Annuleren</a>
            </div>
        </form>
    </div>
</div>
