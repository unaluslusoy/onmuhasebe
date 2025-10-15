<?php

namespace App\Config;

/**
 * Router Configuration and Handler
 * Handles routing logic with middleware support
 */
class Router
{
    private array $routes = [];
    private array $middlewares = [];
    private string $prefix = '';

    /**
     * Add GET route
     */
    public function get(string $path, callable|array $handler, array $middleware = []): self
    {
        return $this->addRoute('GET', $path, $handler, $middleware);
    }

    /**
     * Add POST route
     */
    public function post(string $path, callable|array $handler, array $middleware = []): self
    {
        return $this->addRoute('POST', $path, $handler, $middleware);
    }

    /**
     * Add PUT route
     */
    public function put(string $path, callable|array $handler, array $middleware = []): self
    {
        return $this->addRoute('PUT', $path, $handler, $middleware);
    }

    /**
     * Add DELETE route
     */
    public function delete(string $path, callable|array $handler, array $middleware = []): self
    {
        return $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    /**
     * Add PATCH route
     */
    public function patch(string $path, callable|array $handler, array $middleware = []): self
    {
        return $this->addRoute('PATCH', $path, $handler, $middleware);
    }

    /**
     * Add route with any method
     */
    private function addRoute(string $method, string $path, callable|array $handler, array $middleware = []): self
    {
        $path = $this->prefix . $path;
        
        $this->routes[] = [
            'method' => $method,
            'path' => $path,
            'pattern' => $this->compilePattern($path),
            'handler' => $handler,
            'middleware' => array_merge($this->middlewares, $middleware)
        ];

        return $this;
    }

    /**
     * Group routes with prefix and middleware
     */
    public function group(array $attributes, callable $callback): void
    {
        $previousPrefix = $this->prefix;
        $previousMiddlewares = $this->middlewares;

        if (isset($attributes['prefix'])) {
            $this->prefix .= $attributes['prefix'];
        }

        if (isset($attributes['middleware'])) {
            $this->middlewares = array_merge(
                $this->middlewares,
                (array) $attributes['middleware']
            );
        }

        $callback($this);

        $this->prefix = $previousPrefix;
        $this->middlewares = $previousMiddlewares;
    }

    /**
     * Compile path pattern to regex
     */
    private function compilePattern(string $path): string
    {
        // Convert {param} to regex capture group
        $pattern = preg_replace('/\{([a-zA-Z_][a-zA-Z0-9_]*)\}/', '(?P<$1>[^/]+)', $path);
        return '#^' . $pattern . '$#';
    }

    /**
     * Dispatch request to appropriate handler
     */
    public function dispatch(): void
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove base path if application is in subdirectory
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        if ($scriptName !== '/') {
            $path = substr($path, strlen($scriptName));
        }
        
        $path = '/' . trim($path, '/');

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            if (preg_match($route['pattern'], $path, $matches)) {
                // Extract named parameters
                $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);

                // Build middleware chain
                $middlewareChain = function() use ($route, $params) {
                    // Execute handler
                    if (is_callable($route['handler'])) {
                        call_user_func_array($route['handler'], [$params]);
                    } elseif (is_array($route['handler'])) {
                        [$controller, $method] = $route['handler'];
                        $controllerInstance = new $controller();
                        call_user_func_array([$controllerInstance, $method], [$params]);
                    }
                };

                // Run middleware chain in reverse order
                foreach (array_reverse($route['middleware']) as $middlewareClass) {
                    $next = $middlewareChain;
                    $middlewareChain = function() use ($middlewareClass, $next) {
                        $middleware = new $middlewareClass();
                        return $middleware->handle($next);
                    };
                }

                // Execute the chain
                $middlewareChain();

                return;
            }
        }

        // No route found
        http_response_code(404);
        \App\Helpers\Response::notFound('Route not found');
    }

    /**
     * Get all registered routes
     */
    public function getRoutes(): array
    {
        return $this->routes;
    }
}
