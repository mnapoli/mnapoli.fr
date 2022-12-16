preview: vendor node_modules
	yarn encore dev
	bin/console server:run

assets-dev: node_modules
	yarn encore dev --watch

deploy: vendor node_modules
	php /usr/local/bin/composer install -o --no-dev --no-scripts
	APP_ENV=prod php bin/console cache:clear --no-debug --no-warmup
	APP_ENV=prod php bin/console cache:warmup
	yarn encore production
	serverless deploy
	serverless client deploy --no-confirm
	php /usr/local/bin/composer install

vendor: composer.json composer.lock
	php74 /usr/local/bin/composer install
	yarn install

node_modules: package.json yarn.lock
	yarn
