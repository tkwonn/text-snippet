<?php

require __DIR__ . '/../vendor/autoload.php';

use Exceptions\HttpException;

$routes = include '../src/routes.php';
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path = ltrim($path, '/');

// Look for an exact match first
if (isset($routes[$path])) {
    $handler = $routes[$path];
} else { // Otherwise, iterate over the routes to find a match
    $handler = null;
    foreach ($routes as $pattern => $routeHandler) {
        $regexRoute = '[a-zA-Z0-9_-]{8}';
        if (preg_match('/^' . $regexRoute . '$/', $path, $matches)) {
            $handler = $routes[$regexRoute];
            break;
        }
    }
}

if ($handler) {
    try {
        $renderer = $handler($path);
        // Set raw HTTP headers
        foreach ($renderer->getFields() as $name => $value) {
            $sanitized_value = htmlspecialchars($value, ENT_NOQUOTES, 'UTF-8');
            if ($sanitized_value && $sanitized_value === $value) {
                header("{$name}: {$sanitized_value}");
            } else {
                throw new Exception('Failed setting header - original: ' . $value . ', sanitized: ' . $sanitized_value);
            }
        }
        echo $renderer->getContent();
    } catch (HttpException $e) {
        http_response_code($e->getStatusCode());
        echo $e->getStatusCode() . ' ' . $e->getErrorMessage();
    } catch (Exception $e) {
        http_response_code(500);
        echo $e->getMessage();
    }
} else {
    http_response_code(404);
}
