# Laravel Docker Makefile

.PHONY: help build up down logs shell artisan composer npm test

# Default target
help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Targets:'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  %-15s %s\n", $$1, $$2}' $(MAKEFILE_LIST)

# SSL Certificate
ssl-generate: ## Generate self-signed SSL certificate
	./generate-ssl-cert.sh

# Development commands
dev-build: ## Build development containers
	docker compose --profile dev build

dev-up: ## Start development containers
	@make ssl-generate
	docker compose --profile dev up -d

dev-down: ## Stop development containers
	docker compose --profile dev down

dev-logs: ## Show development logs
	docker compose --profile dev logs -f

dev-shell: ## Enter development container shell
	docker compose --profile dev exec app bash

# Production commands
prod-build: ## Build production containers
	docker compose --profile prod build

prod-up: ## Start production containers
	@make ssl-generate
	docker compose --profile prod up -d

prod-down: ## Stop production containers
	docker compose --profile prod down

prod-logs: ## Show production logs
	docker compose --profile prod logs -f

prod-shell: ## Enter production container shell
	docker compose --profile prod exec app bash

# Laravel commands
artisan: ## Run artisan command (usage: make artisan cmd="migrate")
	docker compose exec app php artisan $(cmd)

migrate: ## Run database migrations
	docker compose exec app php artisan migrate

migrate-fresh: ## Fresh migration with seeding
	docker compose exec app php artisan migrate:fresh --seed

key-generate: ## Generate application key
	docker compose exec app php artisan key:generate

cache-clear: ## Clear all caches
	docker compose exec app php artisan cache:clear
	docker compose exec app php artisan config:clear
	docker compose exec app php artisan route:clear
	docker compose exec app php artisan view:clear

# Composer commands
composer-install: ## Install composer dependencies
	docker compose exec app composer install

composer-update: ## Update composer dependencies
	docker compose exec app composer update

# NPM commands
npm-install: ## Install npm dependencies
	docker compose exec app npm install

npm-dev: ## Run npm development
	docker compose exec app npm run dev

npm-build: ## Build assets for production
	docker compose exec app npm run build

# Testing
test: ## Run PHPUnit tests
	docker compose exec app php artisan test

# Database
db-backup: ## Backup database
	docker compose exec mysql mysqldump -u $(DB_USERNAME) -p$(DB_PASSWORD) $(DB_DATABASE) > backup_$(shell date +%Y%m%d_%H%M%S).sql

db-restore: ## Restore database (usage: make db-restore file="backup.sql")
	docker compose exec -T mysql mysql -u $(DB_USERNAME) -p$(DB_PASSWORD) $(DB_DATABASE) < $(file)

# Ngrok
ngrok-url: ## Get ngrok public URL
	@curl -s http://localhost:4040/api/tunnels | grep -o '"public_url":"[^"]*' | grep -o 'https://[^"]*' | head -1

ngrok-status: ## Check ngrok status
	@curl -s http://localhost:4040/api/tunnels | python3 -m json.tool

# Cleanup
clean: ## Remove all containers and volumes
	docker compose --profile dev down -v
	docker compose --profile prod down -v
	docker system prune -f