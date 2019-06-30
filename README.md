# Plasticode

PHP framework on top of Slim framework.

Sites built on **Plasticode**:

- http://warcry.ru
- http://dacomics.ru
- http://blizzardstreams.com

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
