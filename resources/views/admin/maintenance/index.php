<?php declare(strict_types=1);
/** @var App\Support\View\ViewRenderer $renderer */
/** @var App\Support\Sessions\SessionManager $session */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Onderhoud</h1>
</div>

<div class="row g-4">
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header">Database migraties</div>
            <div class="card-body">
                <p>Voer alle uitstaande database-migraties uit. Bestaande tabellen worden niet overschreven.</p>
                <form method="POST" action="/admin/maintenance/migrations">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($session->get('csrf_token', ''), ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="btn btn-warning">Migraties uitvoeren</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header">Cache wissen</div>
            <div class="card-body">
                <p>Wis alle gecachede data (indien van toepassing).</p>
                <form method="POST" action="/admin/maintenance/clear-cache">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($session->get('csrf_token', ''), ENT_QUOTES, 'UTF-8') ?>">
                    <button type="submit" class="btn btn-outline-secondary">Cache wissen</button>
                </form>
            </div>
        </div>
    </div>
</div>
