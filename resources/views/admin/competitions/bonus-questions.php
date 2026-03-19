<?php declare(strict_types=1);
/** @var App\Domain\Competition\Competition $competition */
/** @var list<array{id: int, prompt: string, question_type: string, entity_type_constraint: ?string, display_order: int}> $questions */
/** @var App\Support\Sessions\SessionManager $session */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Bonusvragen: <?= htmlspecialchars($competition->name, ENT_QUOTES, 'UTF-8') ?></h1>
    <a href="/admin/competitions/<?= $competition->id ?>/edit" class="btn btn-secondary">← Terug</a>
</div>

<div class="card mb-4">
    <div class="card-header">Nieuwe vraag toevoegen</div>
    <div class="card-body">
        <form method="POST" action="/admin/competitions/<?= $competition->id ?>/bonus-questions">
            <input type="hidden" name="<?= htmlspecialchars($session->get('csrf_token_name', '_token'), ENT_QUOTES, 'UTF-8') ?>"
                   value="<?= htmlspecialchars($session->get('csrf_token', ''), ENT_QUOTES, 'UTF-8') ?>">
            <div class="mb-3">
                <label for="prompt" class="form-label">Vraag *</label>
                <input type="text" class="form-control" id="prompt" name="prompt" required>
            </div>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="question_type" class="form-label">Type</label>
                    <select class="form-select" id="question_type" name="question_type">
                        <option value="text">Tekst</option>
                        <option value="entity">Entiteit</option>
                        <option value="number">Getal</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label for="display_order" class="form-label">Volgorde</label>
                    <input type="number" class="form-control" id="display_order" name="display_order" value="0" min="0">
                </div>
                <div class="col-md-4 mb-3 d-flex align-items-end">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1" checked>
                        <label class="form-check-label" for="is_active">Actief</label>
                    </div>
                </div>
            </div>
            <button type="submit" class="btn btn-primary">Toevoegen</button>
        </form>
    </div>
</div>

<h2>Bestaande vragen</h2>
<table class="table table-striped">
    <thead>
        <tr>
            <th>#</th>
            <th>Vraag</th>
            <th>Type</th>
            <th>Volgorde</th>
            <th>Acties</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($questions as $q): ?>
            <tr>
                <td><?= (int)$q['id'] ?></td>
                <td><?= htmlspecialchars($q['prompt'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><code><?= htmlspecialchars($q['question_type'], ENT_QUOTES, 'UTF-8') ?></code></td>
                <td><?= (int)$q['display_order'] ?></td>
                <td>
                    <form method="POST" action="/admin/competitions/<?= $competition->id ?>/bonus-questions/<?= (int)$q['id'] ?>/delete" class="d-inline">
                        <input type="hidden" name="<?= htmlspecialchars($session->get('csrf_token_name', '_token'), ENT_QUOTES, 'UTF-8') ?>"
                               value="<?= htmlspecialchars($session->get('csrf_token', ''), ENT_QUOTES, 'UTF-8') ?>">
                        <button type="submit" class="btn btn-sm btn-outline-danger"
                                onclick="return confirm('Vraag deactiveren?')">Deactiveren</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
