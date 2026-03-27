<?php declare(strict_types=1);
/** @var App\Domain\User\User $user */
/** @var App\Support\Sessions\SessionManager $session */
?>
<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h2 class="mb-0">Gebruiker bewerken</h2>
                <a href="/admin/users" class="btn btn-sm btn-secondary">← Terug</a>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/users/<?= $user->id ?>/edit">
                    <input type="hidden" name="<?= htmlspecialchars($session->get('csrf_token_name', '_token'), ENT_QUOTES, 'UTF-8') ?>"
                           value="<?= htmlspecialchars($session->get('csrf_token', ''), ENT_QUOTES, 'UTF-8') ?>">

                    <div class="mb-3">
                        <label class="form-label">Naam</label>
                        <p class="form-control-static"><?= htmlspecialchars($user->fullName(), ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">E-mail</label>
                        <p class="form-control-static"><?= htmlspecialchars($user->email, ENT_QUOTES, 'UTF-8') ?></p>
                    </div>
                    <div class="mb-3">
                        <label for="role" class="form-label">Rol</label>
                        <select class="form-select" id="role" name="role">
                            <option value="participant" <?= $user->role->value === 'participant' ? 'selected' : '' ?>>Deelnemer</option>
                            <option value="admin" <?= $user->role->value === 'admin' ? 'selected' : '' ?>>Beheerder</option>
                        </select>
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
                               <?= $user->isActive ? 'checked' : '' ?>>
                        <label class="form-check-label" for="is_active">Account actief</label>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Opslaan</button>
                        <a href="/admin/users" class="btn btn-secondary">Annuleren</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
