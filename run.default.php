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
]);
