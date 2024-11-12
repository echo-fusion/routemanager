# Route manager

A lightweight and flexible routing package that helps manage HTTP routes in PHP applications. It supports PSR-7 and PSR-15 standards and is designed for MVC architecture with middleware integration.

## Install

Via Composer

``` bash
$ composer require echo-fusion/routemanager
```

## Requirements

The following versions of PHP are supported by this version.

* PHP 8.1
* PHP 8.2
* PHP 8.3

## Usages

### Basic Setup
1. To get started, you can use the RouteMatcher and Router classes directly.

    ```php
    use EchoFusion\RouteManager\RouteMatch;
    use EchoFusion\RouteManager\Router;
    
    $routeMatcher = new RouteMatch();
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

## Using Provider
The RouteManagerProvider allows you to integrate Router and RouteMatch instances into a dependency injection container.

1. Register the Provider: Add RouteManagerProvider to your service container.

    ```php
    use EchoFusion\RouteManager\Providers\RouteManagerProvider;
    use YourApp\Container;
    
    $provider = new RouteManagerProvider();
    $provider->register($container);
    ```

2. Define Routes Using Configuration: Configure routes by calling the boot method, passing an array of routes or a configuration file.

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

Testing includes PHPUnit and PHPStan (Level 7).

``` bash
$ composer test
```

## Credits
Developed and maintained by [Amir Shadanfar](https://github.com/amir-shadanfar).  
Connect on [LinkedIn](https://www.linkedin.com/in/amir-shadanfar).

## License

The MIT License (MIT). Please see [License File](https://github.com/echo-fusion/routemanager/blob/main/LICENSE) for more information.

