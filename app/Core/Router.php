<?php
/**
 * Router sederhana berbasis parameter ?page=. Setiap "page" dipetakan
 * ke [Controller, method]. Front controller (index.php) memanggil Router::dispatch().
 */
class Router
{
    /** @var array<string, array{0: string, 1: string}> */
    private static array $routes = [];

    public static function get(string $page, string $controller, string $method): void
    {
        self::$routes[$page] = [$controller, $method];
    }

    public static function dispatch(string $page): void
    {
        if (!isset(self::$routes[$page])) {
            http_response_code(404);
            echo "Halaman tidak ditemukan: " . htmlspecialchars($page);
            return;
        }
        [$controllerClass, $method] = self::$routes[$page];
        $controller = new $controllerClass();
        $controller->$method();
    }

    public static function has(string $page): bool
    {
        return isset(self::$routes[$page]);
    }
}
