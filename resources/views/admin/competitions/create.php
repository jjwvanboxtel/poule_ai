<?php declare(strict_types=1);
/** @var App\Support\View\ViewRenderer $renderer */
/** @var App\Support\Sessions\SessionManager $session */
/** @var array<string, string> $errors */
/** @var array<string, string> $old */
?>
<div class="mb-4">
    <a href="/admin/competitions" class="btn btn-sm btn-outline-secondary">&larr; Terug</a>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-dark text-white">
        <h1 class="h5 mb-0">Nieuwe competitie aanmaken</h1>
    </div>
    <div class="card-body">
        <form method="POST" action="/admin/competitions">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($session->get('csrf_token', ''), ENT_QUOTES, 'UTF-8') ?>">

            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label fw-semibold" for="name">Naam <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name" class="form-control <?= isset($errors['name']) ? 'is-invalid' : '' ?>"
                           value="<?= $renderer->e($old['name'] ?? '') ?>" required>
                    <?php if (isset($errors['name'])): ?>
                        <div class="invalid-feedback"><?= $renderer->e($errors['name']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold" for="entry_fee_amount">Inschrijfgeld (€)</label>
                    <input type="number" id="entry_fee_amount" name="entry_fee_amount" class="form-control"
                           step="0.01" min="0" value="<?= $renderer->e($old['entry_fee_amount'] ?? '10.00') ?>">
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold" for="description">Omschrijving</label>
                    <textarea id="description" name="description" class="form-control" rows="3"><?= $renderer->e($old['description'] ?? '') ?></textarea>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold" for="start_date">Startdatum <span class="text-danger">*</span></label>
                    <input type="date" id="start_date" name="start_date" class="form-control"
                           value="<?= $renderer->e($old['start_date'] ?? '') ?>" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold" for="end_date">Einddatum <span class="text-danger">*</span></label>
                    <input type="date" id="end_date" name="end_date" class="form-control"
                           value="<?= $renderer->e($old['end_date'] ?? '') ?>" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold" for="submission_deadline">Deadline inleveren <span class="text-danger">*</span></label>
                    <input type="datetime-local" id="submission_deadline" name="submission_deadline" class="form-control"
                           value="<?= $renderer->e($old['submission_deadline'] ?? '') ?>" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold" for="prize_first_percent">1e prijs (%)</label>
                    <input type="number" id="prize_first_percent" name="prize_first_percent" class="form-control"
                           min="0" max="100" value="<?= $renderer->e($old['prize_first_percent'] ?? '60') ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold" for="prize_second_percent">2e prijs (%)</label>
                    <input type="number" id="prize_second_percent" name="prize_second_percent" class="form-control"
                           min="0" max="100" value="<?= $renderer->e($old['prize_second_percent'] ?? '30') ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold" for="prize_third_percent">3e prijs (%)</label>
                    <input type="number" id="prize_third_percent" name="prize_third_percent" class="form-control"
                           min="0" max="100" value="<?= $renderer->e($old['prize_third_percent'] ?? '10') ?>">
                </div>

                <div class="col-md-6">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" id="is_public" name="is_public" value="1"
                               <?= ($old['is_public'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_public">Openbaar (deelnemers kunnen zelf inschrijven)</label>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Aanmaken</button>
                <a href="/admin/competitions" class="btn btn-link">Annuleren</a>
            </div>
        </form>
    </div>
</div>
