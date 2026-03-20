<?php declare(strict_types=1);
/** @var App\Support\View\ViewRenderer $renderer */
/** @var App\Support\Sessions\SessionManager $session */
/** @var App\Domain\Competition\Competition $competition */
/** @var array<string, mixed> $match */
/** @var array<string, mixed>|null $result */
?>
<div class="mb-4">
    <a href="/admin/competitions/<?= $competition->id ?>/matches" class="btn btn-sm btn-outline-secondary">&larr; Terug</a>
</div>

<div class="card shadow-sm">
    <div class="card-header bg-dark text-white">
        <h1 class="h5 mb-0">
            Uitslag: <?= $renderer->e((string) ($match['home_label'] ?? '')) ?> vs <?= $renderer->e((string) ($match['away_label'] ?? '')) ?>
        </h1>
    </div>
    <div class="card-body">
        <form method="POST" action="/admin/competitions/<?= $competition->id ?>/results/<?= (int) $match['id'] ?>/edit">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($session->get('csrf_token', ''), ENT_QUOTES, 'UTF-8') ?>">

            <div class="row g-3 align-items-center">
                <div class="col-auto">
                    <label class="form-label fw-semibold"><?= $renderer->e((string) ($match['home_label'] ?? 'Thuis')) ?></label>
                    <input type="number" name="home_score" class="form-control text-center"
                           style="width: 80px"
                           min="0" max="99"
                           value="<?= $renderer->e((string) ($result['home_score'] ?? '0')) ?>" required>
                </div>
                <div class="col-auto d-flex align-items-end pb-1">
                    <span class="fs-4 fw-bold">–</span>
                </div>
                <div class="col-auto">
                    <label class="form-label fw-semibold"><?= $renderer->e((string) ($match['away_label'] ?? 'Uit')) ?></label>
                    <input type="number" name="away_score" class="form-control text-center"
                           style="width: 80px"
                           min="0" max="99"
                           value="<?= $renderer->e((string) ($result['away_score'] ?? '0')) ?>" required>
                </div>
            </div>

            <div class="row g-3 mt-2">
                <div class="col-md-3">
                    <label class="form-label">Gele kaarten thuis</label>
                    <input type="number" name="yellow_cards_home" class="form-control"
                           min="0" value="<?= $renderer->e((string) ($result['yellow_cards_home'] ?? '0')) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Gele kaarten uit</label>
                    <input type="number" name="yellow_cards_away" class="form-control"
                           min="0" value="<?= $renderer->e((string) ($result['yellow_cards_away'] ?? '0')) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Rode kaarten thuis</label>
                    <input type="number" name="red_cards_home" class="form-control"
                           min="0" value="<?= $renderer->e((string) ($result['red_cards_home'] ?? '0')) ?>">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Rode kaarten uit</label>
                    <input type="number" name="red_cards_away" class="form-control"
                           min="0" value="<?= $renderer->e((string) ($result['red_cards_away'] ?? '0')) ?>">
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Uitslag opslaan</button>
            </div>
        </form>
    </div>
</div>
