.PHONY: test, check, run, start, restart, ps

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
