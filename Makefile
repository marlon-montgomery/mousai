UID=$(shell id -u)
GID=$(shell id -g)

#-----------------------------------------------------------
# Dev
#-----------------------------------------------------------

dev-up:
	docker-compose up -d

dev-down:
	docker-compose down

dev-status:
	docker-compose ps

dev-logs:
	docker-compose logs -f

dev-restart:
	docker-compose restart

dev-logs-client:
	docker-compose logs -f client

dev-build:
	docker-compose build --build-arg UID=$(UID) --build-arg GID=$(GID)

dev-rebuild: dev-down dev-build

dev-stop:
	docker-compose stop

dev-php-cli:
	docker-compose exec php bash

dev-client-cli:
	docker-compose exec client bash

dev-tinker:
	docker-compose exec php php artisan tinker

dev-composer-install:
	docker-compose exec -T php composer install

dev-optimize:
	docker-compose exec -T php php artisan optimize

dev-migrate:
	docker-compose exec -T php php artisan migrate --path=database/migrations/doors
	docker-compose exec -T php php artisan migrate:refresh
