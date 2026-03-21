<?php declare(strict_types=1);
/** @var App\Support\View\ViewRenderer $renderer */
/** @var App\Support\Sessions\SessionManager $session */
/** @var list<App\Domain\User\User> $users */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Gebruikers beheren</h1>
</div>

<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead class="table-dark">
            <tr>
                <th>Naam</th>
                <th>E-mail</th>
                <th>Rol</th>
                <th>Actief</th>
                <th>Laatste login</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= $renderer->e($u->fullName()) ?></td>
                    <td><?= $renderer->e($u->email) ?></td>
                    <td>
                        <span class="badge <?= $u->isAdmin() ? 'text-bg-danger' : 'text-bg-secondary' ?>">
                            <?= $renderer->e($u->role->value) ?>
                        </span>
                    </td>
                    <td><?= $u->isActive ? '<span class="badge text-bg-success">Ja</span>' : '<span class="badge text-bg-warning">Nee</span>' ?></td>
                    <td><?= $renderer->e($u->lastLoginAt ?? '–') ?></td>
                    <td class="text-end">
                        <div class="d-flex gap-1 justify-content-end">
                            <form method="POST" action="/admin/users/<?= $u->id ?>/role" class="d-flex gap-1">
                                <input type="hidden" name="_token" value="<?= htmlspecialchars($session->get('csrf_token', ''), ENT_QUOTES, 'UTF-8') ?>">
                                <select name="role" class="form-select form-select-sm" style="width:120px">
                                    <option value="admin" <?= $u->isAdmin() ? 'selected' : '' ?>>admin</option>
                                    <option value="participant" <?= $u->isParticipant() ? 'selected' : '' ?>>participant</option>
                                </select>
                                <button type="submit" class="btn btn-sm btn-outline-primary">Rol wijzigen</button>
                            </form>
                            <form method="POST" action="/admin/users/<?= $u->id ?>/status">
                                <input type="hidden" name="_token" value="<?= htmlspecialchars($session->get('csrf_token', ''), ENT_QUOTES, 'UTF-8') ?>">
                                <input type="hidden" name="is_active" value="<?= $u->isActive ? '0' : '1' ?>">
                                <button type="submit" class="btn btn-sm <?= $u->isActive ? 'btn-outline-warning' : 'btn-outline-success' ?>">
                                    <?= $u->isActive ? 'Deactiveren' : 'Activeren' ?>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
