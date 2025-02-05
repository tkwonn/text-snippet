include .env
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

db/reset:
	$(COMPOSE) exec mysql mysql -u$(DATABASE_USER) -p$(DATABASE_USER_PASSWORD) $(DATABASE_NAME) -e "TRUNCATE TABLE $(TABLE_NAME);"

migrate:
	$(COMPOSE) exec php php $(CONSOLE) migrate --init

rollback:
	@if [ -z "$(steps)" ]; then \
			$(COMPOSE) exec php php $(CONSOLE) migrate --rollback; \
	else \
			$(COMPOSE) exec php php $(CONSOLE) migrate --rollback $(steps); \
	fi

seed:
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