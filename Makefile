install:
	composer install
	yarn install
	yarn encore dev

preview:
	php -S 127.0.0.1:8000 -t public bref.php

assets-dev:
	yarn encore dev --watch

deploy:
	vendor/bin/bref deploy
