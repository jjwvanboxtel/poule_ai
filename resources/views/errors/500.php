<?php declare(strict_types=1);
/** @var string $message */
/** @var string $trace */
?>
<div class="text-center py-5">
    <h1 class="display-4">500</h1>
    <p class="lead"><?= htmlspecialchars($message ?? 'Interne fout', ENT_QUOTES, 'UTF-8') ?></p>
    <?php if (!empty($trace)): ?>
        <pre class="text-start bg-light p-3 small"><?= htmlspecialchars($trace, ENT_QUOTES, 'UTF-8') ?></pre>
    <?php endif; ?>
    <a href="/" class="btn btn-primary">Terug naar home</a>
</div>
