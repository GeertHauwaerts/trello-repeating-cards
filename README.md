## About

The app makes it easy to add repeating cards into [Trello](https://trello.com).

## Installation

The installation is very easy and straightforward:

  * Create a `.env` file with your settings.
  * Create a `run.php` file with your settings.
  * Run `composer install` to install the dependencies.

```console
$ cp .env.default .env
$ cp run.default.php run.php
$ composer install
```

## Trello Cards

Create a card that matches the pattern in your `run.php` configuration, make sure that the `#<id>` is set to
day number you want to start repeating.

When creating a repeated card, the app will copy the properties of the highest numbered card, this way you can
easily modify the labels or description of future cards.

> *Note: You can go do [EpochConverter](https://www.epochconverter.com/daynumbers) to get the current day number.*

# Butler Rule

The app does not auto-sort the cards by date, you can add a *Butler Rule* to automate this:

`Rule: when a card is added to list "SCHEDULED" by me, sort the list by due date ascending`

## Development & Testing

To verify the integrity of the codebase you can run the PHP linter:

```console
$ composer install
$ composer phpcs
```

## Collaboration

The GitHub repository is used to keep track of all the bugs and feature
requests; I prefer to work exclusively via GitHib and Twitter.

If you have a patch to contribute:

  * Fork this repository on GitHub.
  * Create a feature branch for your set of patches.
  * Commit your changes to Git and push them to GitHub.
  * Submit a pull request.

Shout to [@GeertHauwaerts](https://twitter.com/GeertHauwaerts) on Twitter at
any time :)

## Donations

If you like this project and you want to support the development, please consider to [donate](https://commerce.coinbase.com/checkout/45c6916d-19ae-40c9-8ef7-7fb7ad30f8e2); all donations are greatly appreciated.

* **[Coinbase Commerce](https://commerce.coinbase.com/checkout/45c6916d-19ae-40c9-8ef7-7fb7ad30f8e2)**: *BTC, BCH, DAI, ETH, LTC, USDC*
* **BTC**: *bc1q654z85zv6sujsjqk750sf4j4eahcckdtq0cqrp*
* **ETH**: *0x4d38b4EB5b0726Dc6bd5770F69348e7472954b41*
* **LTC**: *MBEaP6e4zwro6oNP54yjfC29fVqZ881wdF*
* **DOGE**: *D8LypNzP6GayEBWUKCw3KVc7gwbGBaXynT*
