<?php declare(strict_types=1);

namespace App\Application\Competitions;

use App\Domain\Competition\Competition;
use DomainException;

final class CreateCompetitionService
{
    public function __construct(
        private readonly CompetitionRepositoryInterface $competitions,
    ) {
    }

    /**
     * Create a new competition draft and return it.
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
     *     is_public: bool,
     *     created_by_user_id: int
     * } $data
     * @throws DomainException if prize distribution is invalid or slug is taken.
     */
    public function create(array $data): Competition
    {
        $total = $data['prize_first_percent'] + $data['prize_second_percent'] + $data['prize_third_percent'];
        if ($total !== 100) {
            throw new DomainException(
                "Prijsverdeling moet optellen tot 100% (nu {$total}%).",
            );
        }

        $slug = $this->generateSlug($data['name']);
        $existing = $this->competitions->findBySlug($slug);
        if ($existing !== null) {
            $slug = $slug . '-' . substr(bin2hex(random_bytes(3)), 0, 6);
        }

        $id = $this->competitions->insert([
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
            'is_public' => $data['is_public'] ? 1 : 0,
            'logo_path' => null,
            'created_by_user_id' => $data['created_by_user_id'],
        ]);

        $competition = $this->competitions->findById($id);
        if ($competition === null) {
            throw new DomainException('Kon de nieuw aangemaakte competitie niet ophalen.');
        }

        return $competition;
    }

    private function generateSlug(string $name): string
    {
        $slug = strtolower($name);
        $slug = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $slug) ?: $slug;
        $slug = preg_replace('/[^a-z0-9]+/', '-', $slug) ?? $slug;

        return trim($slug, '-');
    }
}
