<?php declare(strict_types=1);

namespace App\Application\Competitions;

use App\Domain\Competition\Competition;
use DomainException;

final class UpdateCompetitionService
{
    public function __construct(
        private readonly CompetitionRepositoryInterface $competitions,
    ) {
    }

    /**
     * Update an existing competition.
     *
     * @param array{
     *     name: string,
     *     description: string,
     *     start_date: string,
     *     end_date: string,
     *     submission_deadline: string,
     *     entry_fee_amount: float,
     *     prize_first_percent: int,
     *     prize_second_percent: int,
     *     prize_third_percent: int,
     *     status: string,
     *     is_public: bool,
     *     logo_path: string|null
     * } $data
     * @throws DomainException if prize distribution is invalid or competition is not found.
     */
    public function update(int $competitionId, array $data): Competition
    {
        $competition = $this->competitions->findById($competitionId);
        if ($competition === null) {
            throw new DomainException('Competitie niet gevonden.');
        }

        $total = $data['prize_first_percent'] + $data['prize_second_percent'] + $data['prize_third_percent'];
        if ($total !== 100) {
            throw new DomainException(
                "Prijsverdeling moet optellen tot 100% (nu {$total}%).",
            );
        }

        // Only draft competitions may be freely renamed; active/open ones preserve slug
        $slug = $competition->slug;
        if ($competition->isDraft() && strtolower($data['name']) !== strtolower($competition->name)) {
            $newSlug = $this->generateSlug($data['name']);
            $existing = $this->competitions->findBySlug($newSlug);
            if ($existing === null || $existing->id === $competitionId) {
                $slug = $newSlug;
            }
        }

        $this->competitions->update($competitionId, [
            'name' => $data['name'],
            'slug' => $slug,
            'description' => $data['description'],
            'start_date' => $data['start_date'],
            'end_date' => $data['end_date'],
            'submission_deadline' => $data['submission_deadline'],
            'entry_fee_amount' => $data['entry_fee_amount'],
            'prize_first_percent' => $data['prize_first_percent'],
            'prize_second_percent' => $data['prize_second_percent'],
            'prize_third_percent' => $data['prize_third_percent'],
            'status' => $data['status'],
            'is_public' => $data['is_public'] ? 1 : 0,
            'logo_path' => $data['logo_path'],
        ]);

        $updated = $this->competitions->findById($competitionId);
        if ($updated === null) {
            throw new DomainException('Kon de bijgewerkte competitie niet ophalen.');
        }

        return $updated;
    }

    private function generateSlug(string $name): string
    {
        $slug = strtolower($name);
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $slug) ?: $slug;
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? $slug;

        return trim($slug, '-');
    }
}
