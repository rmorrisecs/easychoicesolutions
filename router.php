<?php
/**
 * Local development router mimicking .htaccess pretty URLs.
 * Usage: php -S localhost:8080 router.php
 */
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = rtrim($uri, '/') ?: '/';

$routes = [
    '/' => 'pages/index.html',
    '/about' => 'pages/about.html',
    '/services' => 'pages/services.html',
    '/contact' => 'pages/contact.html',
    '/blog' => 'pages/blog.html',
    '/home' => 'pages/index.html',
];

if (isset($routes[$uri])) {
    $file = __DIR__ . '/' . $routes[$uri];
    if (is_file($file)) {
        header('Content-Type: text/html; charset=UTF-8');
        readfile($file);
        return true;
    }
}

$path = __DIR__ . $uri;
if ($uri !== '/' && is_file($path)) {
    return false; // let the built-in server serve the file
}

http_response_code(404);
header('Content-Type: text/plain; charset=UTF-8');
echo "404 Not Found: $uri";
return true;
