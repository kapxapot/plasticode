# Plasticode

![](https://travis-ci.com/kapxapot/plasticode.svg?branch=master)

PHP framework on top of Slim framework.

Sites built on **Plasticode**:

- https://warcry.ru (GitHub: [kapxapot/plasticode-warcry](https://github.com/kapxapot/plasticode-warcry))
- https://dacomics.ru
- https://bs.warcry.ru (former blizzardstreams.com)
- https://associ.ru (GitHub: [kapxapot/associations](https://github.com/kapxapot/associations))

For boilerplate see [kapxapot/plasticode-boilerplate](https://github.com/kapxapot/plasticode-boilerplate).

## Manual

### Phinx migrations

Plasticode uses [Phinx](http://docs.phinx.org) DB migrations.

#### Run migrations

Run all migrations:

*vendor/bin/phinx migrate*

For non-default environment (stage, production):

*vendor/bin/phinx -e environment*

#### Rollback

Rollback one migration:

*vendor/bin/phinx rollback*

#### New migration

Create new migration:

*vendor/bin/phinx create NameOfMigration*

### Run tests

vendor/bin/phpunit --bootstrap ./vendor/autoload.php tests
