COMPOSE := docker compose
CONSOLE := src/console

build:
	$(COMPOSE) build --no-cache

up:
	$(COMPOSE) up --abort-on-container-exit

upd:
	$(COMPOSE) up -d

stop:
	$(COMPOSE) stop

down:
	$(COMPOSE) down --remove-orphans

clean:
	$(COMPOSE) down --remove-orphans --volumes --rmi all

codegen/migrate:
	@if [ -z "$(name)" ]; then \
		echo "Usage: make codegen/migrate name=YourMigrationName"; \
		exit 1; \
	fi
	$(COMPOSE) exec php php $(CONSOLE) codegen migration --name $(name)

codegen/seeder:
	@if [ -z "$(name)" ]; then \
		echo "Usage: make codegen/seeder name=YourSeederName"; \
		exit 1; \
	fi
	$(COMPOSE) exec php php $(CONSOLE) codegen seeder --name $(name)

db/migrate:
	$(COMPOSE) exec php php $(CONSOLE) migrate --init

db/rollback:
	@if [ -z "$(steps)" ]; then \
			$(COMPOSE) exec php php $(CONSOLE) migrate --rollback; \
	else \
			$(COMPOSE) exec php php $(CONSOLE) migrate --rollback $(steps); \
	fi

db/seed:
	$(COMPOSE) exec php php $(CONSOLE) seed

nginx/reload:
	$(COMPOSE) exec nginx nginx -s reload

php/logs:
	$(COMPOSE) exec php tail -f /var/log/php/error.log

composer/dump-autoload:
	$(COMPOSE) exec php composer dump-autoload

cs-check:
	$(COMPOSE) exec php vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.php --verbose --diff --dry-run

cs-fix:
	$(COMPOSE) exec php vendor/bin/php-cs-fixer fix --config=.php-cs-fixer.php