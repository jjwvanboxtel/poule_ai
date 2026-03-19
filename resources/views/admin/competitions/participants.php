<?php declare(strict_types=1);
/** @var App\Domain\Competition\Competition $competition */
/** @var list<array<string, mixed>> $participants */
/** @var App\Support\Sessions\SessionManager $session */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Deelnemers: <?= htmlspecialchars($competition->name, ENT_QUOTES, 'UTF-8') ?></h1>
    <a href="/admin/competitions/<?= $competition->id ?>/edit" class="btn btn-secondary">← Terug</a>
</div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Naam</th>
            <th>E-mail</th>
            <th>Ingeschreven op</th>
            <th>Betaalstatus</th>
            <th>Acties</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($participants as $p): ?>
            <tr>
                <td><?= htmlspecialchars((string)($p['first_name'] ?? '') . ' ' . (string)($p['last_name'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string)($p['email'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars((string)($p['joined_at'] ?? ''), ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <?php $status = (string)($p['payment_status'] ?? 'unpaid'); ?>
                    <span class="badge <?= $status === 'paid' ? 'bg-success' : 'bg-warning text-dark' ?>">
                        <?= $status === 'paid' ? 'Betaald' : 'Onbetaald' ?>
                    </span>
                </td>
                <td>
                    <form method="POST" action="/admin/competitions/<?= $competition->id ?>/participants/<?= (int)($p['user_id'] ?? 0) ?>/payment" class="d-inline">
                        <input type="hidden" name="<?= htmlspecialchars($session->get('csrf_token_name', '_token'), ENT_QUOTES, 'UTF-8') ?>"
                               value="<?= htmlspecialchars($session->get('csrf_token', ''), ENT_QUOTES, 'UTF-8') ?>">
                        <?php if ($status === 'paid'): ?>
                            <input type="hidden" name="payment_status" value="unpaid">
                            <button type="submit" class="btn btn-sm btn-outline-warning">Markeer onbetaald</button>
                        <?php else: ?>
                            <input type="hidden" name="payment_status" value="paid">
                            <button type="submit" class="btn btn-sm btn-outline-success">Markeer betaald</button>
                        <?php endif; ?>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
