<?php declare(strict_types=1);
/** @var App\Support\View\ViewRenderer $renderer */
/** @var array<string, mixed> $page */
/** @var App\Support\Sessions\SessionManager $session */
$competition = $page['competition'];
$sectionFlags = $page['section_flags'];
$readOnly = (bool) $page['read_only'];
$errors = is_array($page['errors']) ? $page['errors'] : [];
?>
<div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
    <div>
        <h1 class="mb-1"><?= $renderer->e($competition->name) ?></h1>
        <p class="text-muted mb-0">Deadline: <?= $renderer->e($competition->submissionDeadline) ?></p>
    </div>
    <span class="badge <?= $page['participant']['payment_status'] === 'unpaid' ? 'text-bg-warning' : 'text-bg-success' ?>">
        <?= $renderer->e($page['payment_badge']) ?>
    </span>
</div>

<?php if (isset($errors['summary'])): ?>
    <div class="alert alert-danger"><?= $renderer->e($errors['summary']) ?></div>
<?php endif; ?>

<?php if ($readOnly): ?>
    <div class="alert alert-info">
        Deze voorspelling is read-only omdat hij al definitief is ingediend of de deadline is verstreken.
    </div>
<?php endif; ?>

<form method="POST" action="/competitions/<?= $renderer->e($competition->slug) ?>/prediction/submit">
    <input type="hidden" name="<?= $renderer->e($session->get('csrf_token_name', '_token')) ?>"
           value="<?= $renderer->e($session->get('csrf_token', '')) ?>">

    <?php if ($page['matches'] !== []): ?>
        <section class="mb-5">
            <h2 class="h4 mb-3">Wedstrijdvoorspellingen</h2>
            <div class="row g-3">
                <?php foreach ($page['matches'] as $match): ?>
                    <div class="col-12">
                        <div class="card shadow-sm">
                            <div class="card-body">
                                <h3 class="h5"><?= $renderer->e($match['label']) ?></h3>
                                <p class="text-muted small">Fase: <?= $renderer->e($match['stage']) ?> · Aftrap: <?= $renderer->e($match['kickoff_at']) ?></p>

                                <div class="row g-3">
                                    <?php if ($sectionFlags['scores']): ?>
                                        <div class="col-md-3">
                                            <label class="form-label" for="match-<?= $renderer->e($match['id']) ?>-home-score">
                                                Thuisdoelpunten <?= $renderer->e($match['label']) ?>
                                            </label>
                                            <input class="form-control"
                                                   id="match-<?= $renderer->e($match['id']) ?>-home-score"
                                                   type="number"
                                                   min="0"
                                                   name="matches[<?= $renderer->e($match['id']) ?>][predicted_home_score]"
                                                   value="<?= $renderer->e($match['values']['predicted_home_score']) ?>"
                                                   <?= $readOnly ? 'disabled' : '' ?>>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label" for="match-<?= $renderer->e($match['id']) ?>-away-score">
                                                Uitdoelpunten <?= $renderer->e($match['label']) ?>
                                            </label>
                                            <input class="form-control"
                                                   id="match-<?= $renderer->e($match['id']) ?>-away-score"
                                                   type="number"
                                                   min="0"
                                                   name="matches[<?= $renderer->e($match['id']) ?>][predicted_away_score]"
                                                   value="<?= $renderer->e($match['values']['predicted_away_score']) ?>"
                                                   <?= $readOnly ? 'disabled' : '' ?>>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($sectionFlags['outcomes']): ?>
                                        <div class="col-md-6">
                                            <label class="form-label" for="match-<?= $renderer->e($match['id']) ?>-outcome">
                                                Wedstrijdresultaat <?= $renderer->e($match['label']) ?>
                                            </label>
                                            <select class="form-select"
                                                    id="match-<?= $renderer->e($match['id']) ?>-outcome"
                                                    name="matches[<?= $renderer->e($match['id']) ?>][predicted_outcome]"
                                                <?= $readOnly ? 'disabled' : '' ?>>
                                                <option value="">Maak een keuze</option>
                                                <?php foreach (['home_win' => 'Thuis wint', 'draw' => 'Gelijkspel', 'away_win' => 'Uit wint'] as $value => $label): ?>
                                                    <option value="<?= $renderer->e($value) ?>"
                                                        <?= $match['values']['predicted_outcome'] === $value ? 'selected' : '' ?>>
                                                        <?= $renderer->e($label) ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($sectionFlags['cards']): ?>
                                        <div class="col-md-3">
                                            <label class="form-label" for="match-<?= $renderer->e($match['id']) ?>-yellow-home">
                                                Gele kaarten thuis <?= $renderer->e($match['label']) ?>
                                            </label>
                                            <input class="form-control"
                                                   id="match-<?= $renderer->e($match['id']) ?>-yellow-home"
                                                   type="number"
                                                   min="0"
                                                   name="matches[<?= $renderer->e($match['id']) ?>][predicted_yellow_cards_home]"
                                                   value="<?= $renderer->e($match['values']['predicted_yellow_cards_home']) ?>"
                                                   <?= $readOnly ? 'disabled' : '' ?>>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label" for="match-<?= $renderer->e($match['id']) ?>-yellow-away">
                                                Gele kaarten uit <?= $renderer->e($match['label']) ?>
                                            </label>
                                            <input class="form-control"
                                                   id="match-<?= $renderer->e($match['id']) ?>-yellow-away"
                                                   type="number"
                                                   min="0"
                                                   name="matches[<?= $renderer->e($match['id']) ?>][predicted_yellow_cards_away]"
                                                   value="<?= $renderer->e($match['values']['predicted_yellow_cards_away']) ?>"
                                                   <?= $readOnly ? 'disabled' : '' ?>>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label" for="match-<?= $renderer->e($match['id']) ?>-red-home">
                                                Rode kaarten thuis <?= $renderer->e($match['label']) ?>
                                            </label>
                                            <input class="form-control"
                                                   id="match-<?= $renderer->e($match['id']) ?>-red-home"
                                                   type="number"
                                                   min="0"
                                                   name="matches[<?= $renderer->e($match['id']) ?>][predicted_red_cards_home]"
                                                   value="<?= $renderer->e($match['values']['predicted_red_cards_home']) ?>"
                                                   <?= $readOnly ? 'disabled' : '' ?>>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label" for="match-<?= $renderer->e($match['id']) ?>-red-away">
                                                Rode kaarten uit <?= $renderer->e($match['label']) ?>
                                            </label>
                                            <input class="form-control"
                                                   id="match-<?= $renderer->e($match['id']) ?>-red-away"
                                                   type="number"
                                                   min="0"
                                                   name="matches[<?= $renderer->e($match['id']) ?>][predicted_red_cards_away]"
                                                   value="<?= $renderer->e($match['values']['predicted_red_cards_away']) ?>"
                                                   <?= $readOnly ? 'disabled' : '' ?>>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($sectionFlags['bonus_questions'] && $page['bonus_questions'] !== []): ?>
        <section class="mb-5">
            <h2 class="h4 mb-3">Bonusvragen</h2>
            <div class="card shadow-sm">
                <div class="card-body">
                    <?php foreach ($page['bonus_questions'] as $question): ?>
                        <div class="mb-3">
                            <label class="form-label" for="bonus-question-<?= $renderer->e($question['id']) ?>">
                                <?= $renderer->e($question['prompt']) ?>
                            </label>

                            <?php if ($question['question_type'] === 'entity'): ?>
                                <select class="form-select"
                                        id="bonus-question-<?= $renderer->e($question['id']) ?>"
                                        name="bonus_answers[<?= $renderer->e($question['id']) ?>]"
                                    <?= $readOnly ? 'disabled' : '' ?>>
                                    <option value="">Maak een keuze</option>
                                    <?php foreach ($question['options'] as $option): ?>
                                        <option value="<?= $renderer->e($option['id']) ?>"
                                            <?= (string) $question['value'] === (string) $option['id'] ? 'selected' : '' ?>>
                                            <?= $renderer->e($option['display_name']) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            <?php elseif ($question['question_type'] === 'numeric'): ?>
                                <input class="form-control"
                                       id="bonus-question-<?= $renderer->e($question['id']) ?>"
                                       name="bonus_answers[<?= $renderer->e($question['id']) ?>]"
                                       type="number"
                                       min="0"
                                       step="1"
                                       value="<?= $renderer->e($question['value']) ?>"
                                    <?= $readOnly ? 'disabled' : '' ?>>
                            <?php else: ?>
                                <input class="form-control"
                                       id="bonus-question-<?= $renderer->e($question['id']) ?>"
                                       name="bonus_answers[<?= $renderer->e($question['id']) ?>]"
                                       value="<?= $renderer->e($question['value']) ?>"
                                    <?= $readOnly ? 'disabled' : '' ?>>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    <?php endif; ?>

    <?php if ($sectionFlags['knockout'] && $page['knockout_rounds'] !== []): ?>
        <section class="mb-5">
            <h2 class="h4 mb-3">Knock-out rondes</h2>
            <div class="row g-3">
                <?php foreach ($page['knockout_rounds'] as $round): ?>
                    <div class="col-lg-6">
                        <div class="card shadow-sm h-100">
                            <div class="card-body">
                                <h3 class="h5"><?= $renderer->e($round['label']) ?></h3>
                                <?php foreach ($round['slots'] as $slot): ?>
                                    <div class="mb-3">
                                        <label class="form-label" for="round-<?= $renderer->e($round['id']) ?>-slot-<?= $renderer->e($slot['slot_number']) ?>">
                                            <?= $renderer->e($round['label']) ?> · Positie <?= $renderer->e($slot['slot_number']) ?>
                                        </label>
                                        <select class="form-select"
                                                id="round-<?= $renderer->e($round['id']) ?>-slot-<?= $renderer->e($slot['slot_number']) ?>"
                                                name="knockout_rounds[<?= $renderer->e($round['id']) ?>][<?= $renderer->e($slot['slot_number']) ?>]"
                                            <?= $readOnly ? 'disabled' : '' ?>>
                                            <option value="">Maak een keuze</option>
                                            <?php foreach ($round['options'] as $option): ?>
                                                <option value="<?= $renderer->e($option['id']) ?>"
                                                    <?= (string) $slot['value'] === (string) $option['id'] ? 'selected' : '' ?>>
                                                    <?= $renderer->e($option['display_name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php if (!$readOnly): ?>
        <div class="d-flex justify-content-end">
            <button type="submit" class="btn btn-success">Definitief indienen</button>
        </div>
    <?php endif; ?>
</form>
