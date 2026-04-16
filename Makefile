.PHONY: install check test stan pint clean help

.DEFAULT_GOAL := help

help:
	@echo "Webfloo package — dostępne komendy:"
	@echo ""
	@echo "  make install   Install composer dependencies"
	@echo "  make check     Run pint + phpstan + phpunit (full QA)"
	@echo "  make test      PHPUnit tests only"
	@echo "  make stan      PHPStan static analysis (level 10)"
	@echo "  make pint      Laravel Pint style fixer"
	@echo "  make clean     Remove vendor/ + caches"
	@echo ""

install:
	composer install --prefer-dist --no-interaction

check: pint stan test

test:
	./vendor/bin/phpunit

stan:
	./vendor/bin/phpstan analyse --memory-limit=2G

pint:
	./vendor/bin/pint

clean:
	rm -rf vendor/ .phpunit.cache .phpunit.result.cache
