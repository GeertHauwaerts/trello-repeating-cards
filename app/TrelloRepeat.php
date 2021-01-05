<?php

namespace App;

use Carbon\Carbon;
use Trello\Client;
use Trello\Manager;

class TrelloRepeat
{
    private $cfg;
    private $client;
    private $trello;

    public function __construct($cfg = [])
    {
        $this->cfg = $cfg;
        $this->client = new Client();
        $this->authenticate();
        $this->setTrello();
    }

    private function authenticate()
    {
        $required = [
            'TRELLO_API_KEY',
            'TRELLO_API_TOKEN',
        ];

        foreach ($required as $r) {
            if (!isset($_ENV[$r])) {
                $this->error("Missing the environment variable '{$r}'.");
            }
        }

        $this->client->authenticate(
            $_ENV['TRELLO_API_KEY'],
            $_ENV['TRELLO_API_TOKEN'],
            Client::AUTH_URL_CLIENT_ID
        );
    }

    private function setTrello()
    {
        $this->trello = $this->getIdFilter();
        $this->getBoards();
        $this->getCards();
        $this->processIncrementing();
        $this->processDaily();
        $this->processWeekly();
        $this->processMonthly();
        $this->processYearly();
    }

    private function processIncrementing()
    {
        if (!isset($this->cfg['incrementing'])) {
            return;
        }

        foreach ($this->cfg['incrementing'] as $d) {
            $cardQty = 0;
            $cardNum = 0;
            $cardData = [];
            $regex = '/^' . str_replace('{id}', '(\d+)', $d['name']) .'$/';

            foreach ($this->findCards($d['board'], $regex) as $c) {
                $card = $this->getCard($d['board'], $c);

                if (!$card) {
                    $this->error("Unable to fetch the card '{$c}' from the board '{$d['board']}'.");
                }

                preg_match($regex, $card['name'], $matches);

                if (!isset($matches[1])) {
                    $this->error("Unable to find the numeric counter from the card '{$card['name']}'.");
                }

                if ($matches[1] > $cardNum) {
                    $cardNum = $matches[1];
                    $cardData = $card;
                }

                $cardQty++;
            }

            if (!$cardNum) {
                $this->error("Unable to find a card with a numeric counter matching '{$d['name']}'.");
            }

            for ($i = $cardQty; $i < $d['future']; $i++) {
                $cardNum++;

                $create = [
                    'name' => str_replace('{id}', $cardNum, $d['name']),
                    'desc' => $cardData['desc'],
                    'idBoard' => $cardData['idBoard'],
                    'idList' => $cardData['idList'],
                    'idLabels' => implode(',', $cardData['idLabels']),
                ];

                $this->client->api('cards')->create($create);
                $this->log("Added an incrementing card '{$create['name']}'");
            }
        }
    }

    private function processDaily()
    {
        if (!isset($this->cfg['daily'])) {
            return;
        }

        foreach ($this->cfg['daily'] as $d) {
            $cardData = [];
            $regex = '/^' . str_replace('{id}', '(\d+)', $d['name']) .'$/';

            foreach ($this->findCards($d['board'], $regex) as $c) {
                $card = $this->getCard($d['board'], $c);

                if (!$card) {
                    $this->error("Unable to fetch the card '{$c}' from the board '{$d['board']}'.");
                }

                preg_match($regex, $card['name'], $matches);

                if (!isset($matches[1])) {
                    $this->error("Unable to find the day counter from the card '{$card['name']}'.");
                }

                if (!$card['due']) {
                    $this->error("Unable to find the due date from the card '{$card['name']}'.");
                }

                $cardDate = Carbon::parse($card['due']);

                if (empty($cardData)) {
                    $cardData = $card;
                } else {
                    $compareDate = Carbon::parse($cardData['due']);

                    if ($cardDate->gt($compareDate)) {
                        $cardData = $card;
                    }
                }
            }

            if (empty($cardData)) {
                $this->error("Unable to find a card with a numeric counter matching '{$d['name']}'.");
            }

            for ($i = 1; $i < $d['future']; $i++) {
                $carbon = Carbon::parse($cardData['due'])->addDays($i);

                if ($carbon->gt(Carbon::now()->addDays($d['future']))) {
                    break;
                };

                if ($carbon->isWeekend() && isset($d['weekend']) && $d['weekend'] === false) {
                    $d['future']++;
                    continue;
                }

                $create = [
                    'name' => str_replace('{id}', $carbon->dayOfYear, $d['name']),
                    'desc' => $cardData['desc'],
                    'idBoard' => $cardData['idBoard'],
                    'idList' => $cardData['idList'],
                    'idLabels' => implode(',', $cardData['idLabels']),
                    'due' => $carbon->format('c'),
                ];

                $this->client->api('cards')->create($create);
                $this->log("Added a daily card '{$create['name']}'");
            }
        }
    }

    private function processWeekly()
    {
        if (!isset($this->cfg['weekly'])) {
            return;
        }

        foreach ($this->cfg['weekly'] as $d) {
            $cardData = [];
            $regex = '/^' . str_replace('{id}', '(\d+)', $d['name']) .'$/';

            foreach ($this->findCards($d['board'], $regex) as $c) {
                $card = $this->getCard($d['board'], $c);

                if (!$card) {
                    $this->error("Unable to fetch the card '{$c}' from the board '{$d['board']}'.");
                }

                preg_match($regex, $card['name'], $matches);

                if (!isset($matches[1])) {
                    $this->error("Unable to find the week counter from the card '{$card['name']}'.");
                }

                if (!$card['due']) {
                    $this->error("Unable to find the due date from the card '{$card['name']}'.");
                }

                $cardDate = Carbon::parse($card['due']);

                if (empty($cardData)) {
                    $cardData = $card;
                } else {
                    $compareDate = Carbon::parse($cardData['due']);

                    if ($cardDate->gt($compareDate)) {
                        $cardData = $card;
                    }
                }
            }

            if (empty($cardData)) {
                $this->error("Unable to find a card with a numeric counter matching '{$d['name']}'.");
            }

            for ($i = 1; $i < $d['future']; $i++) {
                $carbon = Carbon::parse($cardData['due'])->addWeeks($i);

                if ($carbon->gt(Carbon::now()->addWeeks($d['future']))) {
                    break;
                };

                $create = [
                    'name' => str_replace('{id}', $carbon->weekOfYear, $d['name']),
                    'desc' => $cardData['desc'],
                    'idBoard' => $cardData['idBoard'],
                    'idList' => $cardData['idList'],
                    'idLabels' => implode(',', $cardData['idLabels']),
                    'due' => $carbon->format('c'),
                ];

                $this->client->api('cards')->create($create);
                $this->log("Added a weekly card '{$create['name']}' @ {$carbon->format('Y-m-d H:i:s')}");
            }
        }
    }

    private function processMonthly()
    {
        if (!isset($this->cfg['monthly'])) {
            return;
        }

        foreach ($this->cfg['monthly'] as $d) {
            $cardData = [];
            $regex = '/^' . str_replace('{id}', '(\d+)', $d['name']) .'$/';

            foreach ($this->findCards($d['board'], $regex) as $c) {
                $card = $this->getCard($d['board'], $c);

                if (!$card) {
                    $this->error("Unable to fetch the card '{$c}' from the board '{$d['board']}'.");
                }

                preg_match($regex, $card['name'], $matches);

                if (!isset($matches[1])) {
                    $this->error("Unable to find the month counter from the card '{$card['name']}'.");
                }

                if (!$card['due']) {
                    $this->error("Unable to find the due date from the card '{$card['name']}'.");
                }

                $cardDate = Carbon::parse($card['due']);

                if (empty($cardData)) {
                    $cardData = $card;
                } else {
                    $compareDate = Carbon::parse($cardData['due']);

                    if ($cardDate->gt($compareDate)) {
                        $cardData = $card;
                    }
                }
            }

            if (empty($cardData)) {
                $this->error("Unable to find a card with a numeric counter matching '{$d['name']}'.");
            }

            for ($i = 1; $i < $d['future']; $i++) {
                $carbon = Carbon::parse($cardData['due'])->addMonths($i);

                if ($carbon->gt(Carbon::now()->addMonths($d['future']))) {
                    break;
                };

                if ($d['when']) {
                    $carbon->startOfMonth()->modify($d['when']);
                }

                $create = [
                    'name' => str_replace('{id}', $carbon->month, $d['name']),
                    'desc' => $cardData['desc'],
                    'idBoard' => $cardData['idBoard'],
                    'idList' => $cardData['idList'],
                    'idLabels' => implode(',', $cardData['idLabels']),
                    'due' => $carbon->format('c'),
                ];

                $this->client->api('cards')->create($create);
                $this->log("Added a monthly card '{$create['name']}'");
            }
        }
    }

    private function processYearly()
    {
        if (!isset($this->cfg['yearly'])) {
            return;
        }

        foreach ($this->cfg['yearly'] as $d) {
            $cardData = [];
            $regex = '/^' . str_replace('{id}', '(\d+)', $d['name']) .'$/';
            $regex = str_replace('{uid}', '(\S+)', $regex);

            foreach ($this->findCards($d['board'], $regex) as $c) {
                $card = $this->getCard($d['board'], $c);

                if (!$card) {
                    $this->error("Unable to fetch the card '{$c}' from the board '{$d['board']}'.");
                }

                preg_match($regex, $card['name'], $matches);

                if (!isset($matches[2])) {
                    $this->error("Unable to find the year counter from the card '{$card['name']}'.");
                }

                if (!$card['due']) {
                    $this->error("Unable to find the due date from the card '{$card['name']}'.");
                }

                $cardDate = Carbon::parse($card['due']);

                if (empty($cardData[$matches[1]])) {
                    $cardData[$matches[1]] = $card;
                } else {
                    $compareDate = Carbon::parse($cardData[$matches[1]]['due']);

                    if ($cardDate->gt($compareDate)) {
                        $cardData[$matches[1]] = $card;
                    }
                }
            }

            foreach ($cardData as $uid => $data) {
                for ($i = 1; $i < $d['future']; $i++) {
                    $carbon = Carbon::parse($data['due'])->addYears($i);

                    if ($carbon->gt(Carbon::now()->addYears($d['future']))) {
                        break;
                    };

                    $name = str_replace('{id}', $carbon->year, $d['name']);
                    $name = str_replace('{uid}', $uid, $name);

                    $create = [
                        'name' => $name,
                        'desc' => $data['desc'],
                        'idBoard' => $data['idBoard'],
                        'idList' => $data['idList'],
                        'idLabels' => implode(',', $data['idLabels']),
                        'due' => $carbon->format('c'),
                    ];

                    $this->client->api('cards')->create($create);
                    $this->log("Added a yearly card '{$create['name']}'");
                }
            }
        }
    }

    private function getCard($board, $id)
    {
        foreach ($this->trello[$board]['cards'] as $c) {
            if ($c['id'] === $id) {
                return $c;
            }
        }

        return false;
    }

    private function findCards($board, $title)
    {
        $cards = [];

        if (!isset($this->trello[$board])) {
            return $cards;
        }

        foreach ($this->trello[$board]['cards'] as $c) {
            if (preg_match($title, $c['name'])) {
                $cards[] = $c['id'];
            }
        }

        return $cards;
    }

    private function getIdFilter()
    {
        $filter = [];

        $types = [
            'incrementing',
            'daily',
            'weekly',
            'monthly',
            'yearly',
        ];

        $required = [
            'name',
            'board',
            'future',
        ];

        foreach ($types as $t) {
            if (!isset($this->cfg[$t])) {
                continue;
            }

            foreach ($this->cfg[$t] as $p) {
                foreach ($required as $r) {
                    if (!isset($p[$r])) {
                        $this->error("Missing required parameter '{$r}'.");
                    }

                    if (!isset($filter[$p['board']])) {
                        $filter[$p['board']] = [
                            'cards' => [],
                        ];
                    }
                }
            }
        }

        return $filter;
    }

    private function getBoards()
    {
        $boards = $this->client->api('member')->boards()->all('me');

        foreach ($boards as $b) {
            if (!isset($this->trello[$b['name']])) {
                continue;
            }

            $this->trello[$b['name']]['data'] = $b;
        }

        foreach ($this->trello as $b => $d) {
            if (!isset($this->trello[$b]['data'])) {
                $this->error("Unable to find the board '{$b}'.");
            }
        }
    }

    private function getCards()
    {
        foreach ($this->trello as $b => $d) {
            $this->trello[$b]['cards'] = $this->client->api('boards')->cards()->all($d['data']['id']);
        }
    }

    private function error($msg)
    {
        $this->log($msg);
        exit();
    }

    private function log($msg)
    {
        echo'[' . date('Y-m-d H:i:s') . "] {$msg}\n";
    }
}
