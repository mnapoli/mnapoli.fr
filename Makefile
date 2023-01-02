preview: vendor node_modules
	yarn encore dev
	bin/console server:run

assets-dev: node_modules
	yarn encore dev --watch

deploy: vendor node_modules
	composer install -o --no-dev --no-scripts
	APP_ENV=prod php bin/console cache:clear --no-debug --no-warmup
	APP_ENV=prod php bin/console cache:warmup
	yarn encore production
	npx serverless deploy
	npx serverless client deploy --no-confirm
	composer install

vendor: composer.json composer.lock
	composer install
	yarn install

node_modules: package.json yarn.lock
	yarn
