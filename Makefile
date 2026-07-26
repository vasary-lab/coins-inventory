.PHONY: help

BASEDIR  := $(shell pwd)
DOCKER_COMPOSE_FILE := $(BASEDIR)/docker/docker-compose.yml

help:
	@echo "---------------------------------------------"
	@echo "List of available targets:"
	@echo "  build                   - Builds containers images."
	@echo "  start                   - Starts application in development mode."
	@echo "  stop                    - Stops application containers."
	@echo "  restart                 - Restarts application containers."
	@echo "  shell                   - Opens application container shell."
	@echo "  test                    - Runs application tests."
	@echo "  release                 - Create a release tag and generates changelog"
	@echo "  help                    - Shows this dialog."
	@exit 0

build:
	@echo "Building project..."
	@docker compose -f $(DOCKER_COMPOSE_FILE) build

start:
	@echo "Running project..."
	@docker compose -f $(DOCKER_COMPOSE_FILE) up -d

stop:
	@echo "Stopping project..."
	@docker compose -f $(DOCKER_COMPOSE_FILE) down

shell:
	@docker compose -f $(DOCKER_COMPOSE_FILE) run --rm --no-deps coin-service sh

test:
	@docker compose -f $(DOCKER_COMPOSE_FILE) up -d coin-db
	@docker compose -f $(DOCKER_COMPOSE_FILE) run -T --rm --no-deps coin-service vendor/bin/phpunit -c tests/phpunit.xml --testsuite Tests

code-style:
	@docker compose -f $(DOCKER_COMPOSE_FILE) run -T --rm --no-deps coin-service vendor/bin/phpcs

release:
	@docker compose -f $(DOCKER_COMPOSE_FILE) run -T --rm --no-deps coin-service composer code:release

restart: stop start
