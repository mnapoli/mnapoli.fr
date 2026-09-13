Personal website built with Laravel 13 and [Bref](https://bref.sh).

[mnapoli.fr](https://mnapoli.fr) · Blog posts live in [posts](./posts).

Requires PHP 8.5, Composer 2, and Node.js 24 LTS (see `.php-version` and `.nvmrc`). No database or queue worker is needed.

## Setup and development

```sh
composer run setup
composer run dev
```

Open http://localhost:8000. Vite serves the assets with hot reload.
`make setup` and `make preview` are equivalent shortcuts.

The post editor is available only with `APP_ENV=local`: open `/post` to create an article or `/post/{slug}/edit` to edit one. Preview and image uploads use CSRF-protected web routes. Uploaded images are stored in `public/assets/images` and should be committed with the posts.

## Checks

```sh
npm run build
composer test
composer lint
composer audit
npm audit
```

The Pest smoke suite covers public pages, a Markdown article, Atom output, redirects, missing posts, local editor rendering/preview, and editor isolation outside local development. Build the assets first: the tests exercise the real Vite manifest.

## Deployment

GitHub Actions runs the checks on pull requests and before deploying pushes to `master`. Deployment uses the existing `prod` environment and `BREF_TOKEN` secret.

To deploy manually, install the [Bref CLI](https://bref.sh/docs/cloud/getting-started) (`composer global require bref/cli:^1.1.14`), authenticate, then run:

```sh
make deploy
```

Deployment uses Bref 3 / PHP 8.5 and the open-source Serverless fork [osls 4](https://github.com/oss-serverless/osls), selected with `bref deploy --env=prod --osls4`. The service, domains, and production stage are unchanged.

Vite's hashed files under `public/build/assets` are served by Lift from S3/CloudFront. The manifest stays in the Lambda package so Blade can resolve asset URLs. The existing `/assets/*` mapping continues to serve uploaded images.

Production uses cookie sessions and an in-memory cache; posts remain on disk in the deployment package. Bref builds the configuration cache inside Lambda using the runtime environment. Do not ship locally generated configuration or route caches: local routes would expose the editor.

`npm run package` validates packaging without deploying. Lift/CDK currently emits an intermediate warning about its `HttpApi` reference; the resource exists in the final generated CloudFormation template.

## Upgrade history

The branch upgrades Laravel one major at a time: 10 → 11 (PHP 8.3), 11 → 12 (PHP 8.4), and 12 → 13 (PHP 8.5), with a smoke-tested commit for each step.

Laravel 11 is an intermediate, unsupported version. Resolving its lockfile required Composer's command-only `--no-security-blocking` option. The final dependencies use normal security checks and have no audit exceptions.
