<?php declare(strict_types=1);
/** @var App\Domain\Competition\Competition $competition */
/** @var App\Domain\Competition\CompetitionSection $section */
/** @var list<App\Domain\Competition\CompetitionRule> $rules */
/** @var App\Support\Sessions\SessionManager $session */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Regels: <?= htmlspecialchars($section->label, ENT_QUOTES, 'UTF-8') ?></h1>
    <a href="/admin/competitions/<?= $competition->id ?>/sections" class="btn btn-secondary">← Secties</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/admin/competitions/<?= $competition->id ?>/rules/<?= $section->id ?>">
            <input type="hidden" name="<?= htmlspecialchars($session->get('csrf_token_name', '_token'), ENT_QUOTES, 'UTF-8') ?>"
                   value="<?= htmlspecialchars($session->get('csrf_token', ''), ENT_QUOTES, 'UTF-8') ?>">

            <?php if ($rules === []): ?>
                <div class="alert alert-info">Nog geen regels geconfigureerd. Voeg hieronder regels toe.</div>
            <?php endif; ?>

            <table class="table" id="rules-table">
                <thead>
                    <tr>
                        <th>Regelsleutel</th>
                        <th>Punten</th>
                        <th>Actief</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rules as $i => $rule): ?>
                        <tr>
                            <td>
                                <input type="text" class="form-control form-control-sm" name="rules[<?= $i ?>][rule_key]"
                                       value="<?= htmlspecialchars($rule->ruleKey, ENT_QUOTES, 'UTF-8') ?>" required>
                            </td>
                            <td>
                                <input type="number" class="form-control form-control-sm" name="rules[<?= $i ?>][points_value]"
                                       value="<?= $rule->pointsValue ?>" min="0" style="width:80px">
                            </td>
                            <td>
                                <input type="checkbox" class="form-check-input" name="rules[<?= $i ?>][is_active]" value="1"
                                       <?= $rule->isActive ? 'checked' : '' ?>>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <tr id="new-rule-row">
                        <td>
                            <input type="text" class="form-control form-control-sm" name="rules[<?= count($rules) ?>][rule_key]" placeholder="Nieuwe regelsleutel">
                        </td>
                        <td>
                            <input type="number" class="form-control form-control-sm" name="rules[<?= count($rules) ?>][points_value]" value="1" min="0" style="width:80px">
                        </td>
                        <td>
                            <input type="checkbox" class="form-check-input" name="rules[<?= count($rules) ?>][is_active]" value="1" checked>
                        </td>
                    </tr>
                </tbody>
            </table>

            <button type="submit" class="btn btn-primary">Regels opslaan</button>
        </form>
    </div>
</div>
