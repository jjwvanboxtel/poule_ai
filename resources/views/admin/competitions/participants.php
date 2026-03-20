<?php declare(strict_types=1);
/** @var App\Support\View\ViewRenderer $renderer */
/** @var App\Support\Sessions\SessionManager $session */
/** @var App\Domain\Competition\Competition $competition */
/** @var list<array{id: int, competition_id: int, user_id: int, payment_status: string, joined_at: string, first_name: string, last_name: string, email: string}> $participants */
/** @var list<App\Domain\User\User> $allUsers */
?>
<div class="mb-4">
    <a href="/admin/competitions" class="btn btn-sm btn-outline-secondary">&larr; Terug</a>
</div>

<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h4 mb-0">Deelnemers: <?= $renderer->e($competition->name) ?></h1>
</div>

<?php if ($allUsers !== []): ?>
<div class="card mb-4 shadow-sm">
    <div class="card-header">Deelnemer inschrijven</div>
    <div class="card-body">
        <form method="POST" action="/admin/competitions/<?= $competition->id ?>/participants/enroll" class="row g-2 align-items-end">
            <input type="hidden" name="_token" value="<?= htmlspecialchars($session->get('csrf_token', ''), ENT_QUOTES, 'UTF-8') ?>">
            <div class="col-auto flex-grow-1">
                <select name="user_id" class="form-select" required>
                    <option value="">Selecteer gebruiker...</option>
                    <?php foreach ($allUsers as $u): ?>
                        <option value="<?= $u->id ?>"><?= $renderer->e($u->fullName()) ?> (<?= $renderer->e($u->email) ?>)</option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-auto">
                <button type="submit" class="btn btn-primary">Inschrijven</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php if ($participants === []): ?>
    <div class="alert alert-info">Er zijn nog geen deelnemers ingeschreven.</div>
<?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead class="table-dark">
                <tr>
                    <th>Naam</th>
                    <th>E-mail</th>
                    <th>Betaalstatus</th>
                    <th>Ingeschreven op</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($participants as $p): ?>
                    <tr>
                        <td><?= $renderer->e($p['first_name'] . ' ' . $p['last_name']) ?></td>
                        <td><?= $renderer->e($p['email']) ?></td>
                        <td>
                            <?php if ($p['payment_status'] === 'paid'): ?>
                                <span class="badge text-bg-success">Betaald</span>
                            <?php else: ?>
                                <span class="badge text-bg-warning">Onbetaald</span>
                            <?php endif; ?>
                        </td>
                        <td><?= $renderer->e($p['joined_at']) ?></td>
                        <td class="text-end">
                            <form method="POST" action="/admin/competitions/<?= $competition->id ?>/participants/<?= $p['id'] ?>/payment" class="d-inline">
                                <input type="hidden" name="_token" value="<?= htmlspecialchars($session->get('csrf_token', ''), ENT_QUOTES, 'UTF-8') ?>">
                                <?php if ($p['payment_status'] === 'paid'): ?>
                                    <input type="hidden" name="payment_status" value="unpaid">
                                    <button type="submit" class="btn btn-sm btn-outline-warning">Markeer onbetaald</button>
                                <?php else: ?>
                                    <input type="hidden" name="payment_status" value="paid">
                                    <button type="submit" class="btn btn-sm btn-success">Markeer betaald</button>
                                <?php endif; ?>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>
