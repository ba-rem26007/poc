.PHONY: up down build restart logs test test-backend test-frontend fixtures shell-backend clean

up:
	docker compose up -d

down:
	docker compose down

build:
	docker compose build

restart: down up

logs:
	docker compose logs -f

test: test-backend test-frontend

test-backend:
	cd backend && ./bin/phpunit

test-frontend:
	cd frontend && npm run build

fixtures:
	docker compose exec backend php bin/console doctrine:schema:create --force
	docker compose exec backend php bin/console doctrine:fixtures:load --no-interaction

shell-backend:
	docker compose exec backend bash

clean:
	docker compose down -v --remove-orphans
