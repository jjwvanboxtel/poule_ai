<?php declare(strict_types=1);

namespace App\Domain\Competition;

enum SectionType: string
{
    case GroupStageScores = 'group_stage_scores';
    case MatchOutcomes = 'match_outcomes';
    case Cards = 'cards';
    case Knockout = 'knockout';
    case BonusQuestions = 'bonus_questions';
}
