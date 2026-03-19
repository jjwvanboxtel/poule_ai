<?php declare(strict_types=1);
/** @var string $path */
?>
<div class="text-center py-5">
    <h1 class="display-4">404</h1>
    <p class="lead">Pagina niet gevonden: <code><?= htmlspecialchars($path ?? '', ENT_QUOTES, 'UTF-8') ?></code></p>
    <a href="/" class="btn btn-primary">Terug naar home</a>
</div>
