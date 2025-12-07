.PHONY: help build up down restart logs shell composer artisan migrate seed fresh test clean

# Colors for output
GREEN  := $(shell tput -Txterm setaf 2)
YELLOW := $(shell tput -Txterm setaf 3)
WHITE  := $(shell tput -Txterm setaf 7)
RESET  := $(shell tput -Txterm sgr0)

## Help
help: ## Show this help message
	@echo ''
	@echo 'Usage:'
	@echo '  ${YELLOW}make${RESET} ${GREEN}<target>${RESET}'
	@echo ''
	@echo 'Targets:'
	@awk 'BEGIN {FS = ":.*?## "} { \
		if (/^[a-zA-Z_-]+:.*?##.*$$/) {printf "    ${YELLOW}%-20s${GREEN}%s${RESET}\n", $$1, $$2} \
		else if (/^## .*$$/) {printf "  ${WHITE}%s${RESET}\n", substr($$1,4)} \
		}' $(MAKEFILE_LIST)

## Docker
build: ## Build Docker containers
	docker-compose build

up: ## Start Docker containers
	docker-compose up -d

down: ## Stop Docker containers
	docker-compose down

restart: ## Restart Docker containers
	docker-compose restart

logs: ## View Docker logs
	docker-compose logs -f

logs-app: ## View application logs
	docker-compose logs -f app

logs-db: ## View database logs
	docker-compose logs -f db

ps: ## Show running containers
	docker-compose ps

## Application Setup
setup: ## Complete setup (build, up, install, migrate, seed)
	@make build
	@make up
	@echo "Waiting for containers to be ready..."
	@sleep 10
	@make composer-install
	@make key-generate
	@make migrate
	@make seed
	@echo "${GREEN}Setup complete!${RESET}"

install: ## Install application (same as setup)
	@make setup

## Shell Access
shell: ## Access application container shell
	docker-compose exec app bash

db-shell: ## Access MySQL shell
	docker-compose exec db mysql -u task_user -ptask_password task_management

## Composer
composer-install: ## Install composer dependencies
	docker-compose exec app composer install

composer-update: ## Update composer dependencies
	docker-compose exec app composer update

composer-dump: ## Dump autoload
	docker-compose exec app composer dump-autoload

## Artisan
artisan: ## Run artisan command (usage: make artisan CMD="route:list")
	docker-compose exec app php artisan $(CMD)

key-generate: ## Generate application key
	docker-compose exec app php artisan key:generate

## Database
migrate: ## Run database migrations
	docker-compose exec app php artisan migrate

migrate-fresh: ## Fresh migration (drop all tables)
	docker-compose exec app php artisan migrate:fresh

migrate-rollback: ## Rollback last migration
	docker-compose exec app php artisan migrate:rollback

seed: ## Seed database
	docker-compose exec app php artisan db:seed

fresh: ## Fresh migration with seeding
	docker-compose exec app php artisan migrate:fresh --seed

## Cache
cache-clear: ## Clear all caches
	docker-compose exec app php artisan cache:clear
	docker-compose exec app php artisan config:clear
	docker-compose exec app php artisan route:clear
	docker-compose exec app php artisan view:clear

optimize: ## Optimize application
	docker-compose exec app php artisan config:cache
	docker-compose exec app php artisan route:cache
	docker-compose exec app php artisan view:cache

optimize-clear: ## Clear optimization
	docker-compose exec app php artisan optimize:clear

## Testing
test: ## Run tests
	docker-compose exec app php artisan test

test-coverage: ## Run tests with coverage
	docker-compose exec app php artisan test --coverage

## Permissions
permissions: ## Fix storage and cache permissions
	docker-compose exec app chmod -R 775 storage bootstrap/cache
	docker-compose exec app chown -R www-data:www-data storage bootstrap/cache

## Database Backup
backup: ## Backup database
	docker-compose exec db mysqldump -u task_user -ptask_password task_management > backup_$(shell date +%Y%m%d_%H%M%S).sql
	@echo "${GREEN}Database backed up successfully${RESET}"

restore: ## Restore database (usage: make restore FILE=backup.sql)
	docker-compose exec -T db mysql -u task_user -ptask_password task_management < $(FILE)
	@echo "${GREEN}Database restored successfully${RESET}"

## Cleanup
clean: ## Remove all containers, volumes and images
	docker-compose down -v --rmi all --remove-orphans

clean-volumes: ## Remove only volumes (WARNING: Deletes database)
	docker-compose down -v

## Development
dev: ## Start development environment
	@make up
	@make logs

stop: ## Stop containers
	@make down

rebuild: ## Rebuild containers
	@make down
	@make build
	@make up

## Production
prod-build: ## Build for production
	docker-compose -f docker-compose.yml build --no-cache

prod-up: ## Start production environment
	docker-compose -f docker-compose.yml up -d

## Monitoring
stats: ## Show container stats
	docker stats

top: ## Show running processes in containers
	docker-compose top

## Quick Commands
tinker: ## Run tinker
	docker-compose exec app php artisan tinker

route-list: ## List all routes
	docker-compose exec app php artisan route:list

queue-work: ## Run queue worker
	docker-compose exec app php artisan queue:work

storage-link: ## Create storage link
	docker-compose exec app php artisan storage:link
