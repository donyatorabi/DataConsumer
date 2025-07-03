APP_NAME=symfony_app

up:
	docker compose up -d --build

down:
	docker compose down

install:
	docker compose exec $(APP_NAME) composer install
	docker compose exec $(APP_NAME) php bin/console doctrine:migrations:migrate --no-interaction
	docker compose exec $(APP_NAME) npm install --prefix assets

migrate:
	docker compose exec $(APP_NAME) php bin/console doctrine:migrations:migrate --no-interaction

messenger:
	docker compose exec $(APP_NAME) php bin/console messenger:consume async --time-limit=3600

consumer:
	docker compose exec $(APP_NAME) php bin/console app:consume-rabbitmq

logs:
	docker compose logs -f

bash:
	docker compose exec $(APP_NAME) bash

nginx:
	open http://localhost:8001

rabbit:
	open http://localhost:15673
