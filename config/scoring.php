<?php declare(strict_types=1);

return [
    // Default point values; overridden per competition via competition_rules table.
    'defaults' => [
        'correct_score' => 3,
        'correct_outcome' => 1,
        'correct_goal_diff' => 2,
    ],

    // Bounded evaluator types available for data-driven bonus questions.
    'bonus_evaluator_types' => [
        'exact_match',
        'numeric_range',
        'entity_match',
    ],
];
