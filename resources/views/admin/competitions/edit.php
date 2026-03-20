<?php declare(strict_types=1);
/** @var App\Support\View\ViewRenderer $renderer */
/** @var App\Support\Sessions\SessionManager $session */
/** @var App\Domain\Competition\Competition $competition */
/** @var array<string, string> $errors */
/** @var list<App\Domain\Competition\CompetitionStatus> $statuses */
?>
<div class="mb-4">
    <a href="/admin/competitions" class="btn btn-sm btn-outline-secondary">&larr; Terug</a>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-dark text-white">
        <h1 class="h5 mb-0">Competitie bewerken: <?= $renderer->e($competition->name) ?></h1>
    </div>
    <div class="card-body">
        <form method="POST" action="/admin/competitions/<?= $competition->id ?>/edit" enctype="multipart/form-data">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($session->get('csrf_token', ''), ENT_QUOTES, 'UTF-8') ?>">

            <div class="row g-3">
                <div class="col-md-8">
                    <label class="form-label fw-semibold" for="name">Naam <span class="text-danger">*</span></label>
                    <input type="text" id="name" name="name" class="form-control"
                           value="<?= $renderer->e($competition->name) ?>" required>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold" for="status">Status</label>
                    <select id="status" name="status" class="form-select">
                        <?php foreach ($statuses as $s): ?>
                            <option value="<?= $renderer->e($s->value) ?>" <?= $competition->status === $s ? 'selected' : '' ?>>
                                <?= $renderer->e($s->value) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold" for="description">Omschrijving</label>
                    <textarea id="description" name="description" class="form-control" rows="3"><?= $renderer->e($competition->description) ?></textarea>
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold" for="start_date">Startdatum</label>
                    <input type="date" id="start_date" name="start_date" class="form-control"
                           value="<?= $renderer->e($competition->startDate) ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold" for="end_date">Einddatum</label>
                    <input type="date" id="end_date" name="end_date" class="form-control"
                           value="<?= $renderer->e($competition->endDate) ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold" for="submission_deadline">Deadline</label>
                    <input type="datetime-local" id="submission_deadline" name="submission_deadline" class="form-control"
                           value="<?= $renderer->e(str_replace(' ', 'T', $competition->submissionDeadline)) ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold" for="entry_fee_amount">Inschrijfgeld (€)</label>
                    <input type="number" id="entry_fee_amount" name="entry_fee_amount" class="form-control"
                           step="0.01" min="0" value="<?= $renderer->e((string) $competition->entryFeeAmount) ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold" for="prize_first_percent">1e prijs (%)</label>
                    <input type="number" id="prize_first_percent" name="prize_first_percent" class="form-control"
                           min="0" max="100" value="<?= $renderer->e((string) $competition->prizeFirstPercent) ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold" for="prize_second_percent">2e prijs (%)</label>
                    <input type="number" id="prize_second_percent" name="prize_second_percent" class="form-control"
                           min="0" max="100" value="<?= $renderer->e((string) $competition->prizeSecondPercent) ?>">
                </div>

                <div class="col-md-4">
                    <label class="form-label fw-semibold" for="prize_third_percent">3e prijs (%)</label>
                    <input type="number" id="prize_third_percent" name="prize_third_percent" class="form-control"
                           min="0" max="100" value="<?= $renderer->e((string) $competition->prizeThirdPercent) ?>">
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="logo">Logo uploaden</label>
                    <input type="file" id="logo" name="logo" class="form-control" accept="image/*">
                    <?php if ($competition->logoPath !== null): ?>
                        <small class="text-muted">Huidig logo: <?= $renderer->e($competition->logoPath) ?></small>
                    <?php endif; ?>
                </div>

                <div class="col-md-6">
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" id="is_public" name="is_public" value="1"
                               <?= $competition->isPublic ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_public">Openbaar</label>
                    </div>
                </div>
            </div>

            <div class="mt-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Opslaan</button>
                <a href="/admin/competitions/<?= $competition->id ?>/participants" class="btn btn-outline-secondary">Deelnemers</a>
                <a href="/admin/competitions/<?= $competition->id ?>/matches" class="btn btn-outline-secondary">Wedstrijden</a>
                <a href="/admin/competitions/<?= $competition->id ?>/bonus-questions" class="btn btn-outline-secondary">Bonusvragen</a>
                <a href="/admin/competitions/<?= $competition->id ?>/knockout-rounds" class="btn btn-outline-secondary">Knock-out</a>
                <form method="POST" action="/admin/competitions/<?= $competition->id ?>/recalculate" class="d-inline">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($session->get('csrf_token', ''), ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="btn btn-warning">Herbereken standen</button>
                </form>
            </div>
        </form>
    </div>
</div>
