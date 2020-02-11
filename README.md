# Plasticode

[![Latest Version on Packagist](https://img.shields.io/packagist/v/kapxapot/plasticode.svg)](https://packagist.org/packages/kapxapot/plasticode)
[![Build status](https://img.shields.io/travis/kapxapot/plasticode/master.svg)](https://travis-ci.com/kapxapot/plasticode)
[![Build status](https://travis-ci.com/kapxapot/plasticode.svg?branch=master)](https://travis-ci.com/kapxapot/plasticode)

PHP framework on top of Slim framework.

Sites built on **Plasticode**:

- https://warcry.ru (GitHub: [kapxapot/plasticode-warcry](https://github.com/kapxapot/plasticode-warcry))
- https://dacomics.ru (GitHub: [kapxapot/dacomics](https://github.com/kapxapot/dacomics))
- https://bs.warcry.ru (former blizzardstreams.com) (GitHub: [kapxapot/blizzardstreams](https://github.com/kapxapot/blizzardstreams))
- https://associ.ru (GitHub: [kapxapot/associations](https://github.com/kapxapot/associations))
- FTP Sync (unpublished and abandoned) (GitHub: [kapxapot/ftpsync](https://github.com/kapxapot/ftpsync))

For boilerplate see [kapxapot/plasticode-boilerplate](https://github.com/kapxapot/plasticode-boilerplate).

## Manual

### Phinx migrations

Plasticode uses [Phinx](http://docs.phinx.org) DB migrations.

#### Run migrations

Run all migrations:

```bash
vendor/bin/phinx migrate
```

For non-default environment (stage, production):

```bash
vendor/bin/phinx -e environment
```

#### Rollback

Rollback one migration:

```bash
vendor/bin/phinx rollback
```

#### New migration

Create new migration:

```bash
vendor/bin/phinx create NameOfMigration
```

### Run tests

```bash
composer test
```
