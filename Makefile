.PHONY: test, check, run, start, restart, ps, migrate, consume

start:
	docker-compose up -d

restart:
	docker-compose down && docker-compose build && docker-compose up -d

ps:
	docker-compose ps

test:
	export XDEBUG_MODE=coverage && vendor/bin/phpunit --coverage-html coverage

check:
	php ./vendor/bin/grumphp run

run:
	php ./bin/comments_density analyze:comments

migrate:
	php bin/console doctrine:migration:migrate

consume:
	php bin/console kafka:consumer:run send_message0