<?php declare(strict_types=1);
/** @var App\Support\View\ViewRenderer $renderer */
/** @var App\Support\Sessions\SessionManager $session */
/** @var App\Domain\Competition\Competition $competition */
/** @var list<array{id: int, prompt: string, question_type: string, entity_type_constraint: string|null, display_order: int}> $questions */
/** @var list<array{id: int, entity_type: string, display_name: string, short_code: string|null}> $entities */
?>
<div class="mb-4">
    <a href="/admin/competitions/<?= $competition->id ?>/edit" class="btn btn-sm btn-outline-secondary">&larr; Terug</a>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Bonusvragen: <?= $renderer->e($competition->name) ?></h1>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="/admin/competitions/<?= $competition->id ?>/bonus-questions">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($session->get('csrf_token', ''), ENT_QUOTES, 'UTF-8') ?>">

            <div id="bonus-questions-container">
                <?php foreach ($questions as $i => $q): ?>
                    <div class="card mb-3 bonus-question-row">
                        <div class="card-body">
                            <input type="hidden" name="questions[<?= $i ?>][id]" value="<?= $q['id'] ?>">
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Vraag</label>
                                    <input type="text" name="questions[<?= $i ?>][prompt]"
                                           class="form-control" value="<?= $renderer->e($q['prompt']) ?>" required>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label fw-semibold">Type</label>
                                    <select name="questions[<?= $i ?>][question_type]" class="form-select">
                                        <option value="entity" <?= $q['question_type'] === 'entity' ? 'selected' : '' ?>>Entiteit</option>
                                        <option value="numeric" <?= $q['question_type'] === 'numeric' ? 'selected' : '' ?>>Getal</option>
                                        <option value="text" <?= $q['question_type'] === 'text' ? 'selected' : '' ?>>Tekst</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label fw-semibold">Volgorde</label>
                                    <input type="number" name="questions[<?= $i ?>][display_order]"
                                           class="form-control" value="<?= $q['display_order'] ?>" min="1">
                                </div>
                                <div class="col-md-1 d-flex align-items-end">
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox"
                                               name="questions[<?= $i ?>][is_active]" value="1" checked>
                                        <label class="form-check-label">Actief</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Opslaan</button>
            </div>
        </form>
    </div>
</div>
