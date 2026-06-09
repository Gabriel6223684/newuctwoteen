<?php

declare(strict_types=1);

use Slim\Factory\appFactory;

require __DIR__ . '/../vendor/autoload.php';

// Carrega as variáveis do .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$app = appFactory::create();

$app->addRoutingMiddleware();

$debug = ($_ENV['app_DEBUG'] ?? 'false') === 'true';

$app->addErrorMiddleware($debug, $debug, $debug);

require __DIR__ . '/Helpers/settings.php';
require __DIR__ . '/Routes/routes.php';

return $app;
