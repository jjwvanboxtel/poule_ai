<?php declare(strict_types=1);
/** @var list<App\Domain\User\User> $users */
/** @var App\Support\Sessions\SessionManager $session */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Gebruikersbeheer</h1>
</div>

<table class="table table-striped">
    <thead>
        <tr>
            <th>Naam</th>
            <th>E-mail</th>
            <th>Rol</th>
            <th>Actief</th>
            <th>Geregistreerd</th>
            <th>Acties</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($users as $user): ?>
            <tr>
                <td><?= htmlspecialchars($user->fullName(), ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($user->email, ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <span class="badge <?= $user->isAdmin() ? 'bg-danger' : 'bg-secondary' ?>">
                        <?= htmlspecialchars($user->role->value, ENT_QUOTES, 'UTF-8') ?>
                    </span>
                </td>
                <td>
                    <?php if ($user->isActive): ?>
                        <span class="badge bg-success">Actief</span>
                    <?php else: ?>
                        <span class="badge bg-secondary">Inactief</span>
                    <?php endif; ?>
                </td>
                <td><?= htmlspecialchars($user->createdAt, ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <a href="/admin/users/<?= $user->id ?>/edit" class="btn btn-sm btn-outline-primary">Bewerken</a>
                </td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>
