#!/usr/bin/env bash

Fail() { echo "Error: $@" 1>&2; exit 1; }

set -ex pipefail

test "root" != "$(whoami)" || Fail "Script cannot be executed by root system user"

for c in php composer ; do
  which $c >/dev/null 2>&1 || Fail "$c not found"
done

cd "$(cd `dirname $0` && pwd)/.."

composer install --prefer-dist --no-interaction
composer dump-autoload --optimize --classmap-authoritative

php bin/console doctrine:database:create --if-not-exists
php bin/console doctrine:migrations:migrate --all-or-nothing --no-interaction

php bin/console cache:clear --env=prod --no-debug || rm -rf var/cache/*

