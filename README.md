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
