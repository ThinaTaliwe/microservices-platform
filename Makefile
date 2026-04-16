.PHONY: up down restart ps logs logs-gateway logs-keycloak logs-reports shell-gateway shell-users shell-orders shell-reports

up:
	@docker compose up -d

down:
	@docker compose down

restart:
	@docker compose restart

ps:
	@docker compose ps

logs:
	@docker compose logs -f --tail=50

logs-gateway:
	@docker compose logs -f --tail=100 gateway

logs-keycloak:
	@docker compose logs -f --tail=100 keycloak

logs-reports:
	@docker compose logs -f --tail=100 reports-service

shell-gateway:
	@docker compose exec gateway sh

shell-users:
	@docker compose exec users-php sh

shell-orders:
	@docker compose exec orders-service sh

shell-reports:
	@docker compose exec reports-service sh
