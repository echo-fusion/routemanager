# RouteManager

[![CI](https://github.com/echo-fusion/routemanager/actions/workflows/ci.yml/badge.svg)](https://github.com/echo-fusion/routemanager/actions/workflows/ci.yml) [![Packagist](https://img.shields.io/packagist/v/echo-fusion/routemanager.svg)](https://packagist.org/packages/echo-fusion/routemanager) [![PHP](https://img.shields.io/badge/php-8.1%20%7C%208.2%20%7C%208.3-777bb4.svg)](https://www.php.net) [![License](https://img.shields.io/badge/license-MIT-blue.svg)](LICENSE)

A lightweight and flexible routing package that helps manage HTTP routes in PHP applications. It supports PSR-7 and PSR-15 standards and is designed for MVC architecture with middleware integration.

> Part of [EchoFusion](https://github.com/echo-fusion), a modular PHP framework built from small packages.

> Part of [EchoFusion](https://github.com/echo-fusion), a modular PHP framework built from small packages.

## Installation

```bash
composer require echo-fusion/routemanager
```

## Requirements

* PHP 8.1, 8.2 or 8.3

## Usage

### Basic Setup
1. To get started, you can use the RouteMatcher and Router classes directly.

    ```php
    use EchoFusion\RouteManager\RouteMatcher;
    use EchoFusion\RouteManager\Router;
    
    $routeMatcher = new RouteMatcher();
    $router = new Router($routeMatcher);
    ```

2. Defining Routes
Define routes with optional regex constraints on route parameters. The action parameter can be:

-   An array (e.g., [Controller::class, 'method'])
-   A closure

    ```php
    use Psr\Http\Message\ServerRequestInterface;
    
    // Define a simple route
    $router->post(
        name: 'api.store', 
        path: '/api', 
        action: [ApiController::class, 'store']
    );
    
    // Define a route with parameter constraints
    $router->get(
        name: 'blog-show',
        path: '/post/{id}/detail/{slug}',
        action: function (int $id, string $slug, ServerRequestInterface $request) {
            // Your action code here
            var_dump($id, $slug);
        },
        constraints: [
            'id' => '[0-9]+',
            'slug' => '[a-z0-9\-]+',
        ]
    );
    ```

3. Dispatching and Executing a Route
Once routes are defined, dispatch the request and execute the action if a match is found.

    ```php
    try {
        $routeMatch = $router->dispatch($request);
        
        $action = $routeMatch->getRoute()->getAction();
        $params = array_merge($routeMatch->getParams(), [$request]);
    
        // Execute the action (callable or controller method)
        return call_user_func_array($action, $params);
    } catch (EchoFusion\RouteManager\Exceptions\RouteNotFoundException $e) {
        // Handle route not found
    } catch (Throwable $exception) {
        // Handle other errors
    }
    ```

## Path patterns

```php
$router->get('post', '/post/{id}/{slug}', $action, ['id' => '[0-9]+']);   // constraints array
$router->get('year', '/archive/{year:\d{4}}', $action);                     // inline constraint
```

* A placeholder is `{name}` or `{name:regex}`. A `constraints` entry for the same name wins over the inline regex. Without either, a parameter matches `[a-zA-Z0-9_-]+`.
* Everything outside the placeholders is literal: in `/file.json` the dot is a real dot.
* Parameters are returned by name (`$routeMatch->getParams()`), even when a constraint has capturing groups of its own.
* A path that cannot be compiled (unclosed `{`, duplicate or invalid parameter name, broken regex) throws `Exceptions\InvalidRouteException` **when the route is registered**, not on the first request.
* Methods are compared case-insensitively. A `HEAD` request is served by the `GET` route of the same path.
* One trailing slash on the request is tolerated: a route `/about` also matches `/about/`. A route defined with a trailing slash (`/docs/`) keeps it.
* When nothing matches, `dispatch()` throws `RouteNotFoundException` whose message says what was requested. When the **path** exists but not for the request's method, it throws `MethodNotAllowedException` (a subclass, so existing `catch (RouteNotFoundException)` code keeps working). Use it to answer `405` with an `Allow` header:

```php
try {
    $match = $router->dispatch($request);
} catch (MethodNotAllowedException $e) {
    return $response->withStatus(405)->withHeader('Allow', implode(', ', $e->getAllowedMethods()));
} catch (RouteNotFoundException) {
    return $response->withStatus(404);
}
```

## Routes are values

A `Route` is created complete and never changes (`new Route($name, $method, $path, $action, $constraints, $middlewares)`); `withMiddlewares()` returns a changed copy. You normally do not create routes yourself: the `Router` does when you call `get()`, `post()` ..., or `fromArray()`.

## Registering versus dispatching

The `Router` implements two small contracts, so code depends only on what it uses:

* `EchoFusion\Contracts\RouteManager\RouteRegistrarInterface`: `get()`, `post()`, `put()`, `patch()`, `delete()`, `options()` (the code that defines routes, e.g. a route file).
* `EchoFusion\Contracts\RouteManager\RouterInterface`: `dispatch()`, `getRoute()`, `getRoutes()` (the code that handles requests).

## Using Provider

The RouteManagerProvider allows you to integrate the Router and RouteMatcher instances into a dependency injection container.

1. Register the Provider: Add RouteManagerProvider to your service container.

    ```php
    use EchoFusion\RouteManager\Providers\RouteManagerProvider;
    use YourApp\Container;
    
    $provider = new RouteManagerProvider();
    $provider->register($container);
    ```

2. Define Routes Using Configuration: Configure routes by calling the boot method, passing an array of routes or a configuration file. Only `method`, `path` and `action` are required; `constraints` and `middlewares` are optional.

    ```php
    $routes = [
        'home' => [
            'method' => 'GET',
            'path' => '/',
            'action' => fn () => 'Welcome to the homepage',
        ],
        'blog_show' => [
            'method' => 'GET',
            'path' => '/post/{id}/detail/{slug}',
            'action' => function (int $id, string $slug, ServerRequestInterface $request) {
                // Route action code here
            },
            'constraints' => [
                'id' => '[0-9]+',
                'slug' => '[a-z0-9\-]+',
            ],
        ],
    ];
    
    $provider->boot($container, $routes);
    ```
    If no routes are provided, the boot method defaults to loading routes from routemanager.config.php.

3. Dispatching a Route: With the routes registered, retrieve the RouterInterface from the container and dispatch it with a request object.

    ```php
    use EchoFusion\Contracts\RouteManager\RouterInterface;
    use EchoFusion\RouteManager\Exceptions\RouteNotFoundException;
    
    try {
        $router = $container->get(RouterInterface::class);
        $routeMatch = $router->dispatch($request);
    } catch (RouteNotFoundException $e) {
        // Handle route not found
    } catch (Throwable $exception) {
        // Handle other errors
    }
    ```

## Testing

```bash
composer test
```

## Credits

- [Amir Shadanfar](https://github.com/amir-shadanfar)

## License

The MIT License (MIT). Please see [LICENSE](LICENSE) for more information.
