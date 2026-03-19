<?php declare(strict_types=1);
/** @var App\Support\View\ViewRenderer $renderer */
/** @var array<string, string> $errors */
/** @var array<string, string> $old */
/** @var string $intended */
/** @var App\Support\Sessions\SessionManager $session */
?>
<div class="row justify-content-center">
    <div class="col-lg-5">
        <h1 class="mb-4">Inloggen</h1>
        <form method="POST" action="/login" class="card shadow-sm">
            <div class="card-body">
                <input type="hidden" name="<?= $renderer->e($session->get('csrf_token_name', '_token')) ?>"
                       value="<?= $renderer->e($session->get('csrf_token', '')) ?>">
                <input type="hidden" name="intended" value="<?= $renderer->e($intended) ?>">

                <div class="mb-3">
                    <label class="form-label" for="email">E-mailadres</label>
                    <input class="form-control" id="email" name="email" type="email" required
                           value="<?= $renderer->e($old['email'] ?? '') ?>">
                    <?php if (isset($errors['email'])): ?>
                        <div class="text-danger small mt-1"><?= $renderer->e($errors['email']) ?></div>
                    <?php endif; ?>
                </div>

                <div class="mb-3">
                    <label class="form-label" for="password">Wachtwoord</label>
                    <input class="form-control" id="password" name="password" type="password" required>
                </div>
            </div>
            <div class="card-footer d-flex justify-content-between align-items-center">
                <span class="text-muted small">Nog geen account? <a href="/register">Registreer hier</a></span>
                <button type="submit" class="btn btn-primary">Inloggen</button>
            </div>
        </form>
    </div>
</div>
