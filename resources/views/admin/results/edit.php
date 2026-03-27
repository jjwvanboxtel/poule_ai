<?php declare(strict_types=1);
/** @var App\Domain\Competition\Competition $competition */
/** @var array<string, mixed> $match */
/** @var App\Domain\Competition\MatchResult|null $result */
/** @var App\Support\Sessions\SessionManager $session */
$matchId = (int)($match['id'] ?? 0);
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Uitslag invoeren</h1>
    <a href="/admin/competitions/<?= $competition->id ?>/matches" class="btn btn-secondary">← Wedstrijden</a>
</div>

<div class="card">
    <div class="card-header">
        <?= htmlspecialchars(is_scalar($match['home_label'] ?? null) ? (string)$match['home_label'] : '', ENT_QUOTES, 'UTF-8') ?>
        vs
        <?= htmlspecialchars(is_scalar($match['away_label'] ?? null) ? (string)$match['away_label'] : '', ENT_QUOTES, 'UTF-8') ?>
    </div>
    <div class="card-body">
        <form method="POST" action="/admin/competitions/<?= $competition->id ?>/results/<?= $matchId ?>/edit">
            <input type="hidden" name="<?= htmlspecialchars($session->get('csrf_token_name', '_token'), ENT_QUOTES, 'UTF-8') ?>"
                   value="<?= htmlspecialchars($session->get('csrf_token', ''), ENT_QUOTES, 'UTF-8') ?>">

            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Thuisstand</label>
                    <input type="number" class="form-control" name="home_score" min="0"
                           value="<?= $result?->homeScore !== null ? $result->homeScore : '' ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Uitstand</label>
                    <input type="number" class="form-control" name="away_score" min="0"
                           value="<?= $result?->awayScore !== null ? $result->awayScore : '' ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Uitslag</label>
                    <select class="form-select" name="result_outcome">
                        <option value="">Onbekend</option>
                        <option value="home_win" <?= $result?->resultOutcome === 'home_win' ? 'selected' : '' ?>>Thuiswinst</option>
                        <option value="draw" <?= $result?->resultOutcome === 'draw' ? 'selected' : '' ?>>Gelijkspel</option>
                        <option value="away_win" <?= $result?->resultOutcome === 'away_win' ? 'selected' : '' ?>>Uitwinst</option>
                    </select>
                </div>
            </div>
            <div class="row">
                <div class="col-md-3 mb-3">
                    <label class="form-label">Gele kaarten thuis</label>
                    <input type="number" class="form-control" name="yellow_cards_home" min="0" value="<?= $result?->yellowCardsHome ?? 0 ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Gele kaarten uit</label>
                    <input type="number" class="form-control" name="yellow_cards_away" min="0" value="<?= $result?->yellowCardsAway ?? 0 ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Rode kaarten thuis</label>
                    <input type="number" class="form-control" name="red_cards_home" min="0" value="<?= $result?->redCardsHome ?? 0 ?>">
                </div>
                <div class="col-md-3 mb-3">
                    <label class="form-label">Rode kaarten uit</label>
                    <input type="number" class="form-control" name="red_cards_away" min="0" value="<?= $result?->redCardsAway ?? 0 ?>">
                </div>
            </div>

            <button type="submit" class="btn btn-primary">Uitslag opslaan</button>
        </form>
    </div>
</div>
