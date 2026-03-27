<?php declare(strict_types=1);
/** @var App\Support\Sessions\SessionManager $session */
?>
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h2 class="mb-0">Nieuwe competitie aanmaken</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="/admin/competitions" enctype="multipart/form-data">
                    <input type="hidden" name="<?= htmlspecialchars($session->get('csrf_token_name', '_token'), ENT_QUOTES, 'UTF-8') ?>"
                           value="<?= htmlspecialchars($session->get('csrf_token', ''), ENT_QUOTES, 'UTF-8') ?>">

                    <div class="mb-3">
                        <label for="name" class="form-label">Naam *</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="slug" class="form-label">Slug *</label>
                        <input type="text" class="form-control" id="slug" name="slug" required
                               placeholder="bijv. ek-2026">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Beschrijving</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="start_date" class="form-label">Startdatum *</label>
                            <input type="date" class="form-control" id="start_date" name="start_date" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="end_date" class="form-label">Einddatum *</label>
                            <input type="date" class="form-control" id="end_date" name="end_date" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="submission_deadline" class="form-label">Deadline inzendingen *</label>
                        <input type="datetime-local" class="form-control" id="submission_deadline" name="submission_deadline" required>
                    </div>
                    <div class="mb-3">
                        <label for="entry_fee_amount" class="form-label">Inschrijfgeld (€)</label>
                        <input type="number" class="form-control" id="entry_fee_amount" name="entry_fee_amount"
                               step="0.01" min="0" value="0.00">
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="prize_first_percent" class="form-label">1e prijs (%)</label>
                            <input type="number" class="form-control" id="prize_first_percent" name="prize_first_percent"
                                   min="0" max="100" value="60">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="prize_second_percent" class="form-label">2e prijs (%)</label>
                            <input type="number" class="form-control" id="prize_second_percent" name="prize_second_percent"
                                   min="0" max="100" value="30">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="prize_third_percent" class="form-label">3e prijs (%)</label>
                            <input type="number" class="form-control" id="prize_third_percent" name="prize_third_percent"
                                   min="0" max="100" value="10">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="logo" class="form-label">Logo</label>
                        <input type="file" class="form-control" id="logo" name="logo" accept="image/*">
                    </div>
                    <div class="mb-3 form-check">
                        <input type="checkbox" class="form-check-input" id="is_public" name="is_public" value="1" checked>
                        <label class="form-check-label" for="is_public">Publiek zichtbaar</label>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Aanmaken</button>
                        <a href="/admin/competitions" class="btn btn-secondary">Annuleren</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
