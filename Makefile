.PHONY: php


SUPPORTED_COMMANDS := php composer cube43 test test-and-bdd coverage cs static cbf test-module coverage-module
SUPPORTS_MAKE_ARGS := $(findstring $(firstword $(MAKECMDGOALS)), $(SUPPORTED_COMMANDS))
ifneq "$(SUPPORTS_MAKE_ARGS)" ""
  COMMAND_ARGS := $(wordlist 2,$(words $(MAKECMDGOALS)),$(MAKECMDGOALS))
  COMMAND_ARGS := $(subst :,\:,$(COMMAND_ARGS))
  $(eval $(COMMAND_ARGS):;@:)
endif

DOCKER_PHP := docker compose exec php

ifeq (, $(shell which docker compose))
	BASE :=
else
	BASE := $(DOCKER_PHP)
endif

# Display general help about this command
help:
	@echo "Makefile."

# alias for help target
all: help

# Container management

dup:
    ifeq ($(BASE), $(DOCKER_PHP))
		docker compose up -d --remove-orphans
    endif

kill:
	docker compose rm -f -s

login:
	docker compose exec php sh

php:
	$(BASE) $(COMMAND_ARGS)

# Dépendence

install: dup
	$(BASE) composer install

update: dup
	$(BASE) composer update

composer: dup
	$(BASE) composer $(COMMAND_ARGS)

composer-valid: dup
	$(BASE) composer validate

# Tests

test:
	$(BASE) php -dpcov.enabled=0 vendor/bin/phpunit $(COMMAND_ARGS)

coverage: dup
	$(BASE) ./vendor/bin/phpunit --coverage-html coverage $(COMMAND_ARGS)

coverage-open:
	firefox coverage/index.html

infection: dup
	$(BASE) phpdbg -qrr -d memory_limit=-1 ./vendor/bin/infection -j=5 $(COMMAND_ARGS)

# Analyse

phpstan:
	$(BASE) php -d memory_limit=1700M vendor/bin/phpstan analyse --memory-limit 1700M $(COMMAND_ARGS)

phpstan-baseline: dup
	$(BASE) vendor/bin/phpstan analyse --memory-limit 1000M --generate-baseline $(COMMAND_ARGS)

psalm:
	$(BASE) php -d memory_limit=1700M vendor/bin/psalm $(COMMAND_ARGS) --show-info=true

is-valid: test cs phpstan psalm composer-valid

cs:
	$(BASE) php -d memory_limit=-1 ./vendor/bin/phpcs $(COMMAND_ARGS)

cs-clear-cache:
	rm .phpcs-cache

cbf: dup
	$(BASE) php -d memory_limit=-1 ./vendor/bin/phpcbf $(COMMAND_ARGS)