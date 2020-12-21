<?php

use App\TrelloRepeat;
use Dotenv\Dotenv;

require __DIR__ . '/vendor/autoload.php';

(Dotenv::createImmutable(__DIR__))->load();

$app = new TrelloRepeat([
    'incrementing' => [
        [
            'name' => 'DEV-{id}:',
            'board' => 'Development Backlog',
            'future' => 10,
        ],
    ],
    'daily' => [
        [
            'name' => 'My First Daily Task #{id}',
            'board' => 'Operations',
            'future' => 7,
        ],
        [
            'name' => 'My Second Daily Task #{id}',
            'board' => 'Operations',
            'future' => 7,
        ],
    ],
    'weekly' => [
        [
            'name' => 'Weekly Progress Report #{id}',
            'board' => 'Operations',
            'future' => 2,
        ],
    ],
    'monthly' => [
        [
            'name' => 'Monthly Finance Check #{id}',
            'when' => 'Second Monday 7pm',
            'board' => 'Operations',
            'future' => 2,
        ],
    ],
    'yearly' => [
        [
            'name' => 'Domain Renewal - {uid} - {id}',
            'board' => 'Operations',
            'future' => 2,
        ],
    ],
]);
