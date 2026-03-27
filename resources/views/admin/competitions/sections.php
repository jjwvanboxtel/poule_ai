<?php declare(strict_types=1);
/** @var App\Domain\Competition\Competition $competition */
/** @var list<App\Domain\Competition\CompetitionSection> $sections */
/** @var list<App\Domain\Competition\SectionType> $sectionTypes */
/** @var App\Support\Sessions\SessionManager $session */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1>Secties: <?= htmlspecialchars($competition->name, ENT_QUOTES, 'UTF-8') ?></h1>
    <a href="/admin/competitions/<?= $competition->id ?>/edit" class="btn btn-secondary">← Terug</a>
</div>

<div class="card">
    <div class="card-body">
        <form method="POST" action="/admin/competitions/<?= $competition->id ?>/sections">
            <input type="hidden" name="<?= htmlspecialchars($session->get('csrf_token_name', '_token'), ENT_QUOTES, 'UTF-8') ?>"
                   value="<?= htmlspecialchars($session->get('csrf_token', ''), ENT_QUOTES, 'UTF-8') ?>">

            <table class="table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Label</th>
                        <th>Actief</th>
                        <th>Volgorde</th>
                        <th>Regels</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($sectionTypes as $i => $sectionType): ?>
                        <?php
                        $existing = null;
                        foreach ($sections as $s) {
                            if ($s->sectionType === $sectionType) {
                                $existing = $s;
                                break;
                            }
                        }
                        ?>
                        <tr>
                            <td>
                                <input type="hidden" name="sections[<?= $i ?>][section_type]" value="<?= htmlspecialchars($sectionType->value, ENT_QUOTES, 'UTF-8') ?>">
                                <code><?= htmlspecialchars($sectionType->value, ENT_QUOTES, 'UTF-8') ?></code>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm" name="sections[<?= $i ?>][label]"
                                       value="<?= htmlspecialchars($existing?->label ?? $sectionType->name, ENT_QUOTES, 'UTF-8') ?>">
                            </td>
                            <td>
                                <input type="checkbox" class="form-check-input" name="sections[<?= $i ?>][is_active]" value="1"
                                       <?= ($existing?->isActive ?? false) ? 'checked' : '' ?>>
                            </td>
                            <td>
                                <input type="number" class="form-control form-control-sm" name="sections[<?= $i ?>][display_order]"
                                       value="<?= $existing?->displayOrder ?? $i ?>" min="0" style="width:80px">
                            </td>
                            <td>
                                <?php if ($existing !== null): ?>
                                    <a href="/admin/competitions/<?= $competition->id ?>/rules/<?= $existing->id ?>"
                                       class="btn btn-sm btn-outline-info">Regels</a>
                                <?php else: ?>
                                    <span class="text-muted small">Sla eerst op</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <button type="submit" class="btn btn-primary">Secties opslaan</button>
        </form>
    </div>
</div>
