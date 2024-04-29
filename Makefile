preview:
	make -j2 preview-parallel
preview-parallel: preview-assets preview-php
preview-assets: node_modules
	npm run hot
preview-php: vendor
	php artisan serve

deploy: vendor node_modules
	composer install -o --no-dev --ignore-platform-reqs
	npm run prod
	npx serverless deploy
	composer install --ignore-platform-reqs

vendor: composer.json composer.lock
	composer install --ignore-platform-reqs

node_modules: package.json package-lock.json
	npm ci
