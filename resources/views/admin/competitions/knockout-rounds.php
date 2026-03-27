<?php declare(strict_types=1);
/** @var App\Domain\Competition\Competition $competition */
/** @var list<array<string, mixed>> $rounds */
/** @var App\Support\Sessions\SessionManager $session */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Knockout rondes: <?= htmlspecialchars($competition->name, ENT_QUOTES, 'UTF-8') ?></h1>
    <a href="/admin/competitions/<?= $competition->id ?>/edit" class="btn btn-secondary">← Terug</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/admin/competitions/<?= $competition->id ?>/knockout-rounds">
            <input type="hidden" name="<?= htmlspecialchars($session->get('csrf_token_name', '_token'), ENT_QUOTES, 'UTF-8') ?>"
                   value="<?= htmlspecialchars($session->get('csrf_token', ''), ENT_QUOTES, 'UTF-8') ?>">

            <table class="table" id="rounds-table">
                <thead>
                    <tr>
                        <th>Label</th>
                        <th>Volgorde</th>
                        <th>Aantal slots</th>
                        <th>Actief</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rounds as $i => $round): ?>
                        <tr>
                            <?php if (is_numeric($round['id'] ?? null)): ?>
                                <input type="hidden" name="rounds[<?= $i ?>][id]" value="<?= (int)$round['id'] ?>">
                            <?php endif; ?>
                            <td>
                                <input type="text" class="form-control form-control-sm" name="rounds[<?= $i ?>][label]"
                                       value="<?= htmlspecialchars(is_scalar($round['label'] ?? null) ? (string)$round['label'] : '', ENT_QUOTES, 'UTF-8') ?>" required>
                            </td>
                            <td>
                                <input type="number" class="form-control form-control-sm" name="rounds[<?= $i ?>][round_order]"
                                       value="<?= is_numeric($round['round_order'] ?? null) ? (int)$round['round_order'] : $i ?>" min="0" style="width:80px">
                            </td>
                            <td>
                                <input type="number" class="form-control form-control-sm" name="rounds[<?= $i ?>][team_slot_count]"
                                       value="<?= is_numeric($round['team_slot_count'] ?? null) ? (int)$round['team_slot_count'] : 2 ?>" min="1" style="width:80px">
                            </td>
                            <td>
                                <input type="checkbox" class="form-check-input" name="rounds[<?= $i ?>][is_active]" value="1"
                                       <?= !empty($round['is_active']) ? 'checked' : '' ?>>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                    <?php $newIdx = count($rounds); ?>
                    <?php $nextOrder = $rounds === [] ? 0 : (int) max(array_column($rounds, 'round_order')) + 1; ?>
                    <tr>
                        <td><input type="text" class="form-control form-control-sm" name="rounds[<?= $newIdx ?>][label]" placeholder="Nieuwe ronde"></td>
                        <td><input type="number" class="form-control form-control-sm" name="rounds[<?= $newIdx ?>][round_order]" value="<?= $nextOrder ?>" min="0" style="width:80px"></td>
                        <td><input type="number" class="form-control form-control-sm" name="rounds[<?= $newIdx ?>][team_slot_count]" value="2" min="1" style="width:80px"></td>
                        <td><input type="checkbox" class="form-check-input" name="rounds[<?= $newIdx ?>][is_active]" value="1" checked></td>
                    </tr>
                </tbody>
            </table>

            <button type="submit" class="btn btn-primary">Opslaan</button>
        </form>
    </div>
</div>
