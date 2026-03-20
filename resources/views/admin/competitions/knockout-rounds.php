<?php declare(strict_types=1);
/** @var App\Support\View\ViewRenderer $renderer */
/** @var App\Support\Sessions\SessionManager $session */
/** @var App\Domain\Competition\Competition $competition */
/** @var list<array{id: int, label: string, round_order: int, team_slot_count: int}> $rounds */
/** @var list<array{id: int, entity_type: string, display_name: string, short_code: string|null}> $entities */
?>
<div class="mb-4">
    <a href="/admin/competitions/<?= $competition->id ?>/edit" class="btn btn-sm btn-outline-secondary">&larr; Terug</a>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Knock-out rondes: <?= $renderer->e($competition->name) ?></h1>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <form method="POST" action="/admin/competitions/<?= $competition->id ?>/knockout-rounds">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($session->get('csrf_token', ''), ENT_QUOTES, 'UTF-8') ?>">

            <?php foreach ($rounds as $i => $r): ?>
                <div class="card mb-3">
                    <div class="card-body">
                        <div class="row g-2">
                            <div class="col-md-4">
                                <label class="form-label fw-semibold">Label</label>
                                <input type="text" name="rounds[<?= $i ?>][label]"
                                       class="form-control" value="<?= $renderer->e($r['label']) ?>" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Volgorde</label>
                                <input type="number" name="rounds[<?= $i ?>][round_order]"
                                       class="form-control" value="<?= $r['round_order'] ?>" min="1" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-semibold">Aantal slots</label>
                                <input type="number" name="rounds[<?= $i ?>][team_slot_count]"
                                       class="form-control" value="<?= $r['team_slot_count'] ?>" min="2" max="64" required>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox"
                                           name="rounds[<?= $i ?>][is_active]" value="1" checked>
                                    <label class="form-check-label">Actief</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if ($rounds === []): ?>
                <p class="text-muted">Nog geen knock-out rondes geconfigureerd.</p>
            <?php endif; ?>

            <div class="mt-3 d-flex gap-2">
                <button type="submit" class="btn btn-primary">Opslaan</button>
            </div>
        </form>
    </div>
</div>
