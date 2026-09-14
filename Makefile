.PHONY: setup preview test build deploy

setup:
	composer run setup

preview: vendor node_modules
	php artisan dev

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
