<?php declare(strict_types=1);
/** @var App\Support\View\ViewRenderer $renderer */
/** @var array<string, mixed> $page */
$competition = $page['competition'];
$submission = $page['submission'];
?>
<div class="card shadow-sm">
    <div class="card-body">
        <h1 class="h3 mb-3">Voorspelling bevestigd</h1>
        <p class="lead">Je voorspelling voor <strong><?= $renderer->e($competition->name) ?></strong> is definitief opgeslagen.</p>
        <ul class="list-unstyled mb-4">
            <li><strong>Status:</strong> Read-only</li>
            <li><strong>Ingediend op:</strong> <?= $renderer->e($submission?->submittedAt ?? '') ?></li>
            <li><strong>Betaling:</strong> <?= $renderer->e($page['payment_badge']) ?></li>
        </ul>
        <a class="btn btn-primary" href="/competitions/<?= $renderer->e($competition->slug) ?>/prediction">Bekijk mijn read-only voorspelling</a>
        <a class="btn btn-link" href="/dashboard">Terug naar dashboard</a>
    </div>
</div>
