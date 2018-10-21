<?php
use App\Kernel;
use Bref\Bridge\Symfony\SymfonyAdapter;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Symfony\Component\Debug\Debug;
use Symfony\Component\Dotenv\Dotenv;
use Zend\Diactoros\Response\JsonResponse;

// Serve static files when running with PHP's built-in webserver
if (php_sapi_name() === 'cli-server' && is_file(__DIR__ . '/public' . preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']))) {
    return false;
}

require __DIR__.'/vendor/autoload.php';

Debug::enable();

// The check is to ensure we don't use .env in production
if (!isset($_SERVER['APP_ENV'])) {
    (new Dotenv)->load(__DIR__.'/.env');
}
if ($_SERVER['APP_DEBUG'] ?? ('prod' !== ($_SERVER['APP_ENV'] ?? 'dev'))) {
    umask(0000);
}
$kernel = new Kernel($_SERVER['APP_ENV'] ?? 'dev', (bool) ($_SERVER['APP_DEBUG'] ?? ('prod' !== ($_SERVER['APP_ENV'] ?? 'dev'))));

$app = new \Bref\Application;
$app->httpHandler(new SymfonyAdapter($kernel));
$app->cliHandler(new \Symfony\Bundle\FrameworkBundle\Console\Application($kernel));
$app->run();
