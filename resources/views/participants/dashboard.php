<?php declare(strict_types=1);
/** @var App\Support\View\ViewRenderer $renderer */
/** @var App\Domain\User\User $user */
/** @var list<array{id: int, name: string, slug: string, submission_deadline: string, status: string, payment_status: string, joined_at: string}> $joinedCompetitions */
/** @var list<array{id: int, name: string, slug: string, submission_deadline: string}> $openCompetitions */
?>
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="mb-1">Welkom, <?= $renderer->e($user->fullName()) ?></h1>
        <p class="text-muted mb-0">Beheer je deelnames en lever je voorspelling op tijd in.</p>
    </div>
</div>

<section class="mb-5">
    <h2 class="h4 mb-3">Mijn competities</h2>

    <?php if ($joinedCompetitions === []): ?>
        <div class="alert alert-info">Je bent nog niet gekoppeld aan een competitie.</div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($joinedCompetitions as $competition): ?>
                <div class="col-md-6">
                    <div class="card h-100 shadow-sm">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h3 class="h5 mb-0"><?= $renderer->e($competition['name']) ?></h3>
                                <?php if ($competition['payment_status'] === 'unpaid'): ?>
                                    <span class="badge text-bg-warning">Onbetaald</span>
                                <?php else: ?>
                                    <span class="badge text-bg-success">Betaald</span>
                                <?php endif; ?>
                            </div>
                            <p class="text-muted small mb-3">Deadline: <?= $renderer->e($competition['submission_deadline']) ?></p>
                            <a class="btn btn-primary btn-sm" href="/competitions/<?= $renderer->e($competition['slug']) ?>/prediction">
                                Voorspelling bekijken
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>

<section>
    <h2 class="h4 mb-3">Open competities</h2>

    <?php if ($openCompetitions === []): ?>
        <div class="alert alert-secondary mb-0">Er zijn momenteel geen extra competities open voor zelfinschrijving.</div>
    <?php else: ?>
        <div class="row g-3">
            <?php foreach ($openCompetitions as $competition): ?>
                <div class="col-md-6">
                    <div class="card h-100 border-primary-subtle">
                        <div class="card-body">
                            <h3 class="h5"><?= $renderer->e($competition['name']) ?></h3>
                            <p class="text-muted small">Open voor inschrijving tot <?= $renderer->e($competition['submission_deadline']) ?></p>
                            <a class="btn btn-outline-primary btn-sm" href="/competitions/<?= $renderer->e($competition['slug']) ?>/prediction">
                                Voorspelling invullen
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</section>
