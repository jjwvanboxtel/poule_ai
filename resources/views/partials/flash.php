<?php declare(strict_types=1);
/** @var App\Support\Sessions\SessionManager $session */
$success = $session->getFlash('success');
$error = $session->getFlash('error');
$info = $session->getFlash('info');
$warning = $session->getFlash('warning');
?>
<?php if ($success !== null): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <?= htmlspecialchars((string) $success, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Sluiten"></button>
    </div>
<?php endif; ?>
<?php if ($error !== null): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <?= htmlspecialchars((string) $error, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Sluiten"></button>
    </div>
<?php endif; ?>
<?php if ($info !== null): ?>
    <div class="alert alert-info alert-dismissible fade show" role="alert">
        <?= htmlspecialchars((string) $info, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Sluiten"></button>
    </div>
<?php endif; ?>
<?php if ($warning !== null): ?>
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <?= htmlspecialchars((string) $warning, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Sluiten"></button>
    </div>
<?php endif; ?>
