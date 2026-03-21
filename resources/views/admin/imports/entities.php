<?php declare(strict_types=1);
/** @var App\Support\View\ViewRenderer $renderer */
/** @var App\Support\Sessions\SessionManager $session */
/** @var list<App\Domain\Competition\Competition> $competitions */
/** @var array<string, string> $errors */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Entiteiten importeren (CSV)</h1>
</div>

<div class="row g-4">
    <div class="col-md-7">
        <div class="card shadow-sm">
            <div class="card-header">CSV bestand uploaden</div>
            <div class="card-body">
                <form method="POST" action="/admin/imports/entities" enctype="multipart/form-data">
                    <input type="hidden" name="_token" value="<?= htmlspecialchars($session->get('csrf_token', ''), ENT_QUOTES, 'UTF-8') ?>">

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="competition_id">Competitie (optioneel)</label>
                        <select name="competition_id" id="competition_id" class="form-select">
                            <option value="0">Globaal (geen specifieke competitie)</option>
                            <?php foreach ($competitions as $c): ?>
                                <option value="<?= $c->id ?>"><?= $renderer->e($c->name) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold" for="csv_file">CSV bestand <span class="text-danger">*</span></label>
                        <input type="file" id="csv_file" name="csv_file" class="form-control <?= isset($errors['file']) ? 'is-invalid' : '' ?>"
                               accept=".csv,text/csv" required>
                        <?php if (isset($errors['file'])): ?>
                            <div class="invalid-feedback"><?= $renderer->e($errors['file']) ?></div>
                        <?php endif; ?>
                    </div>

                    <button type="submit" class="btn btn-primary">Importeren</button>
                </form>
            </div>
        </div>
    </div>
    <div class="col-md-5">
        <div class="card bg-light">
            <div class="card-body">
                <h5 class="card-title">CSV formaat</h5>
                <p class="small">Verplichte kolommen: <code>display_name</code>, <code>entity_type</code></p>
                <p class="small">Optioneel: <code>short_code</code>, <code>nationality</code>, <code>is_active</code></p>
                <p class="small mb-1">Geldige entity_type waarden:</p>
                <ul class="small mb-0">
                    <li><code>country</code> – land</li>
                    <li><code>team</code> – club/team</li>
                    <li><code>player</code> – speler</li>
                    <li><code>referee</code> – scheidsrechter</li>
                    <li><code>coach</code> – trainer</li>
                    <li><code>other</code> – overig</li>
                </ul>
                <hr>
                <p class="small text-muted">Voorbeeld:</p>
                <pre class="small bg-white p-2 rounded">display_name,entity_type,short_code
Nederland,country,NED
Duitsland,country,GER</pre>
            </div>
        </div>
    </div>
</div>
