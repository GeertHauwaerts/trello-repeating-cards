<?php

use App\TrelloRepeat;
use Dotenv\Dotenv;

require __DIR__ . '/vendor/autoload.php';

(Dotenv::createImmutable(__DIR__))->load();

$app = new TrelloRepeat([
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
