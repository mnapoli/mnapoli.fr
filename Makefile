.PHONY: setup preview preview-assets preview-php test build deploy

setup:
	composer run setup

preview:
	$(MAKE) -j2 preview-assets preview-php

preview-assets: node_modules
	npm run dev

preview-php: vendor
	php artisan serve

test: vendor
	composer test

build: vendor node_modules
	npm run build

deploy: build
	composer install --no-dev --optimize-autoloader
	bref deploy --env=prod --osls4; status=$$?; composer install; exit $$status

vendor: composer.json composer.lock
	composer install

node_modules: package.json package-lock.json
	npm ci
