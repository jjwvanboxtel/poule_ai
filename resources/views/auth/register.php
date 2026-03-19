<?php declare(strict_types=1);
/** @var App\Support\View\ViewRenderer $renderer */
/** @var array<string, string> $errors */
/** @var array<string, string> $old */
/** @var App\Support\Sessions\SessionManager $session */
?>
<div class="row justify-content-center">
    <div class="col-lg-6">
        <h1 class="mb-4">Registreren</h1>
        <form method="POST" action="/register" class="card shadow-sm">
            <div class="card-body">
                <input type="hidden" name="<?= $renderer->e($session->get('csrf_token_name', '_token')) ?>"
                       value="<?= $renderer->e($session->get('csrf_token', '')) ?>">

                <div class="mb-3">
                    <label class="form-label" for="first_name">Voornaam</label>
                    <input class="form-control" id="first_name" name="first_name" required
                           value="<?= $renderer->e($old['first_name'] ?? '') ?>">
                    <?php if (isset($errors['first_name'])): ?>
                        <div class="text-danger small mt-1"><?= $renderer->e($errors['first_name']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="last_name">Achternaam</label>
                    <input class="form-control" id="last_name" name="last_name" required
                           value="<?= $renderer->e($old['last_name'] ?? '') ?>">
                    <?php if (isset($errors['last_name'])): ?>
                        <div class="text-danger small mt-1"><?= $renderer->e($errors['last_name']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="email">E-mailadres</label>
                    <input class="form-control" id="email" name="email" type="email" required
                           value="<?= $renderer->e($old['email'] ?? '') ?>">
                    <?php if (isset($errors['email'])): ?>
                        <div class="text-danger small mt-1"><?= $renderer->e($errors['email']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="phone_number">Telefoonnummer</label>
                    <input class="form-control" id="phone_number" name="phone_number" required
                           value="<?= $renderer->e($old['phone_number'] ?? '') ?>">
                    <?php if (isset($errors['phone_number'])): ?>
                        <div class="text-danger small mt-1"><?= $renderer->e($errors['phone_number']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="password">Wachtwoord</label>
                    <input class="form-control" id="password" name="password" type="password" required minlength="8">
                    <?php if (isset($errors['password'])): ?>
                        <div class="text-danger small mt-1"><?= $renderer->e($errors['password']) ?></div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center">
                <span class="text-muted small">Heb je al een account? <a href="/login">Log in</a></span>
                <button type="submit" class="btn btn-primary">Account aanmaken</button>
            </div>
        </form>
    </div>
</div>
