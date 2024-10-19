# Route manager

A lightweight and flexible routing package that helps manage HTTP routes in PHP applications. Supports PSR-7 and PSR-15, designed for MVC architecture with middleware integration.

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

```php
use EchoFusion\RouteManager\Router;
use EchoFusion\RouteManager\RouteMatcher\RouteMatcher;

$routeMatcher = new RouteMatcher();
$router = new Router($routeMatcher);
```

After initialize router, you can define add your routes with optional Regex constraints check on route parameters.

Action parameter in Route can be:
-   array (controller, method)
-   closure

```php
use Psr\Http\Message\ServerRequestInterface;

// simple route
$router->post(
    name: 'api.store', 
    path: '/api', 
    action: [ApiController::class, 'store']
);

// route parameter with regex constraints
$router->get(
    name: 'blog-show',
    path: '/post/{id}/detail/{slug}',
    action: function(int $id, string $slug,  ServerRequestInterface $request) {
        // write your code here...           
        var_dump($id , $slug);
    }),
    constraints: [
        'id' => '[0-9]+',
        'slug' => '[a-z0-9\-]+',
    ]
);
```

If request matched with any defined route, it will return instance of RouteMatchInterface.

```php
try {
    $routeMatch = $router->dispatch($request);
} catch (RouteNotFoundException $e) {
    // Handle route not found
} catch (Throwable $exception) {
    // Handle any other errors
}
```

Now, you can call controller with given method: 

```php
$action = $routeMatch->getRoute()->getAction();

// Get the route parameters and request
$routeParams = $routeMatch->getParams();
$params = array_merge($routeParams, [$request]);

// Call the action (callable or controller method) with type hinting
return call_user_func_array($action, $params);
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

