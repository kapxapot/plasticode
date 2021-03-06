# Plasticode

[![Latest Version on Packagist](https://img.shields.io/packagist/v/kapxapot/plasticode.svg)](https://packagist.org/packages/kapxapot/plasticode)
[![Build Status](https://travis-ci.com/kapxapot/plasticode.svg?branch=master)](https://travis-ci.com/kapxapot/plasticode)
[![Coverage Status](https://coveralls.io/repos/github/kapxapot/plasticode/badge.svg?branch=master)](https://coveralls.io/github/kapxapot/plasticode?branch=master)

PHP framework on top of Slim framework.

Sites built on **Plasticode**:

|Project|GitHub|
|-------|------|
|[Warcry.ru](https://warcry.ru)|[kapxapot/plasticode-warcry](https://github.com/kapxapot/plasticode-warcry)|
|[DAComics.ru](https://dacomics.ru)|[kapxapot/dacomics](https://github.com/kapxapot/dacomics)|
|[Blizzard Streams](https://bs.warcry.ru) (former blizzardstreams.com)|[kapxapot/blizzardstreams](https://github.com/kapxapot/blizzardstreams)|
|[Ассоциации](https://associ.ru)|[kapxapot/associations](https://github.com/kapxapot/associations)|
|FTP Sync (unpublished and abandoned)|[kapxapot/ftpsync](https://github.com/kapxapot/ftpsync)|
|Boilerplate|[kapxapot/plasticode-boilerplate](https://github.com/kapxapot/plasticode-boilerplate)|

## Manual

### Phinx migrations

Plasticode uses [Phinx](http://docs.phinx.org) DB migrations.

#### Run all migrations

```bash
vendor/bin/phinx migrate
```

For non-default environment (stage, production):

```bash
vendor/bin/phinx -e environment
```

#### Rollback one migration

```bash
vendor/bin/phinx rollback
```

#### Create new migration

```bash
vendor/bin/phinx create NameOfMigration
```

### Run tests

```bash
composer test
```
