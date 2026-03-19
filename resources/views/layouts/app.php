<?php declare(strict_types=1);
/** @var App\Support\View\ViewRenderer $renderer */
/** @var string $title */
/** @var string $content */
/** @var App\Support\Sessions\SessionManager $session */
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= htmlspecialchars($title ?? 'Voetbalpoule', ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') ?> – Voetbalpoule</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
          integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
          crossorigin="anonymous">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <div class="container">
        <a class="navbar-brand" href="/">Voetbalpoule</a>
        <div class="navbar-nav ms-auto">
            <?php if ($session->get('user_id')): ?>
                <a class="nav-link" href="/dashboard">Dashboard</a>
                <?php if ($session->get('user_role') === 'admin'): ?>
                    <a class="nav-link" href="/admin">Admin</a>
                <?php endif; ?>
                <form method="POST" action="/logout" class="d-inline">
                    <input type="hidden" name="<?= htmlspecialchars($session->get('csrf_token_name', '_token'), ENT_QUOTES, 'UTF-8') ?>"
                           value="<?= htmlspecialchars($session->get('csrf_token', ''), ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="btn btn-link nav-link">Uitloggen</button>
                </form>
            <?php else: ?>
                <a class="nav-link" href="/login">Inloggen</a>
                <a class="nav-link" href="/register">Registreren</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<main class="container py-4">
    <?php include __DIR__ . '/../partials/flash.php'; ?>
    <?= $content ?>
</main>
<footer class="bg-light py-3 mt-4">
    <div class="container text-center text-muted small">
        &copy; <?= date('Y') ?> Voetbalpoule
    </div>
</footer>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-C6RzsynM9kWDrMNeT87bh95OGNyZPhcTNXj1NW7RuBCsyN/o0jlpcV8Qyq46cDfL"
        crossorigin="anonymous"></script>
</body>
</html>
