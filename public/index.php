<?php
ini_set('display_errors', 0);
ini_set('display_startup_errors', 0);
error_reporting(E_ALL);
set_error_handler(function($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
}, E_ALL);

require_once '../vendor/autoload.php';

use Dotenv\Dotenv;
use CORSProxy\Server;
use CORSProxy\ProxyRequest;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;


$logger = new Logger('cors-proxy');
$logger->pushHandler(new StreamHandler('php://stderr', Logger::DEBUG));
try {
    if (file_exists(__DIR__.'/../.env')) {
        $dotenv = new Dotenv(__DIR__.'/../');
        $dotenv->load();
    }
    $allowOrigin = getenv('ALLOW_ORIGIN') ?: 'http://localhost:8080';
    $authKeyDigest = getenv('AUTH_KEY_DIGEST') ?: null;

    $server = new Server($allowOrigin, $authKeyDigest);
    $response = $server->execute(new ProxyRequest($_GET, $_SERVER));

    foreach ($response->getHeaders() as $key => $value) {
        header("$key: $value");
    }
    http_response_code($response->getStatusCode());
    print($response->getBody());
} catch (Throwable $e) {
    $logger->error($e);
    http_response_code(502);
}
