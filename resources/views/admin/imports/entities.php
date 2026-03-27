<?php declare(strict_types=1);
/** @var App\Domain\Competition\Competition $competition */
/** @var App\Support\Sessions\SessionManager $session */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Entiteiten importeren</h1>
    <a href="/admin/competitions/<?= $competition->id ?>/edit" class="btn btn-secondary">← Terug</a>
</div>

<div class="card">
    <div class="card-header"><?= htmlspecialchars($competition->name, ENT_QUOTES, 'UTF-8') ?></div>
    <div class="card-body">
        <p class="text-muted">Upload een CSV-bestand met kolommen: <code>entity_type, display_name, short_code, nationality, is_active</code></p>

        <form method="POST" action="/admin/competitions/<?= $competition->id ?>/import/entities" enctype="multipart/form-data">
            <input type="hidden" name="<?= htmlspecialchars($session->get('csrf_token_name', '_token'), ENT_QUOTES, 'UTF-8') ?>"
                   value="<?= htmlspecialchars($session->get('csrf_token', ''), ENT_QUOTES, 'UTF-8') ?>">

            <div class="mb-3">
                <label for="csv_file" class="form-label">CSV-bestand *</label>
                <input type="file" class="form-control" id="csv_file" name="csv_file" accept=".csv,text/csv" required>
            </div>

            <button type="submit" class="btn btn-primary">Importeren</button>
        </form>
    </div>
</div>
