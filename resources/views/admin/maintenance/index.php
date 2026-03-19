<?php declare(strict_types=1);
/** @var list<array<string, mixed>> $logs */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Onderhoud &amp; Audit log</h1>
</div>

<div class="card">
    <div class="card-header">Recente beheeracties</div>
    <div class="card-body p-0">
        <table class="table table-sm table-striped mb-0">
            <thead>
                <tr>
                    <th>Tijdstip</th>
                    <th>Beheerder</th>
                    <th>Actie</th>
                    <th>Entity type</th>
                    <th>Entity ID</th>
                    <th>Details</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($logs as $log): ?>
                    <tr>
                        <td><small><?= htmlspecialchars(is_scalar($log['created_at'] ?? null) ? (string)$log['created_at'] : '', ENT_QUOTES, 'UTF-8') ?></small></td>
                        <td><?= htmlspecialchars(
                            trim((is_scalar($log['first_name'] ?? null) ? (string)$log['first_name'] : '') . ' ' .
                                 (is_scalar($log['last_name'] ?? null) ? (string)$log['last_name'] : '')),
                            ENT_QUOTES, 'UTF-8'
                        ) ?></td>
                        <td><code><?= htmlspecialchars(is_scalar($log['action'] ?? null) ? (string)$log['action'] : '', ENT_QUOTES, 'UTF-8') ?></code></td>
                        <td><?= htmlspecialchars(is_scalar($log['entity_type'] ?? null) ? (string)$log['entity_type'] : '', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars(is_scalar($log['entity_id'] ?? null) ? (string)$log['entity_id'] : '-', ENT_QUOTES, 'UTF-8') ?></td>
                        <td><small><?= htmlspecialchars(is_scalar($log['details_json'] ?? null) ? (string)$log['details_json'] : '', ENT_QUOTES, 'UTF-8') ?></small></td>
                    </tr>
                <?php endforeach; ?>
                <?php if ($logs === []): ?>
                    <tr><td colspan="6" class="text-center text-muted">Geen recente acties</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
