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
        $this->processDaily();
    }

    private function processDaily()
    {
        foreach ($this->cfg['daily'] as $d) {
            $dayNum = 0;
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

                if ($matches[1] > $dayNum) {
                    $dayNum = $matches[1];
                    $cardData = $card;
                }
            }

            if (!$dayNum) {
                $this->error("Unable to find a card with a numeric counter matching '{$d['name']}'.");
            }

            for ($i = 0; $i < $d['future']; $i++) {
                $dayNum++;
                $carbon = Carbon::createFromFormat('z Y H:i', '0 ' . date('Y') . ' 21:00')->addDays($dayNum - 1);

                if ($carbon->gt(Carbon::now()->addDays($d['future']))) {
                    break;
                };

                $create = [
                    'name' => str_replace('{id}', $dayNum, $d['name']),
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

        $required = [
            'name',
            'board',
            'future',
        ];

        foreach ($this->cfg['daily'] as $p) {
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
