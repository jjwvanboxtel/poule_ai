<?php declare(strict_types=1);

namespace App\Domain\Competition;

enum CompetitionStatus: string
{
    case Draft = 'draft';
    case Active = 'active';
    case Open = 'open';
    case Closed = 'closed';
    case Archived = 'archived';
}
