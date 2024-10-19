<?php

declare(strict_types=1);

namespace EchoFusion\RouteManager;

use Closure;
use EchoFusion\RouteManager\Exceptions\DuplicateRouteException;
use EchoFusion\RouteManager\Exceptions\RouteNotFoundException;
use EchoFusion\RouteManager\RouteMatch\RouteMatchInterface;
use EchoFusion\RouteManager\RouteMatcher\RouteMatcherInterface;
use Psr\Http\Message\ServerRequestInterface;

class Router implements RouterInterface
{
    /**
     * @var array<Route>
     */
    private array $routes = [];

    public function __construct(
        protected readonly RouteMatcherInterface $routeMatcher,
    ) {
    }

    public function getRoutes(): array
    {
        return $this->routes;
    }

    public function getRoute(string $name): ?Route
    {
        if (key_exists($name, $this->routes)) {
            return $this->routes[$name];
        }

        return null;
    }

    /**
     * @throws RouteNotFoundException
     */
    public function dispatch(ServerRequestInterface $request): RouteMatchInterface
    {
        foreach ($this->routes as $route) {
            $routeMatch = $this->routeMatcher->match($request, $route);
            if ($routeMatch instanceof RouteMatchInterface) {
                return $routeMatch;
            }
        }

        throw new RouteNotFoundException();
    }

    public function get(string $name, string $path, array|Closure $action, ?array $constraints = []): self
    {
        return $this->register(HttpMethod::GET, $name, $path, $action, $constraints);
    }

    public function post(string $name, string $path, array|Closure $action, ?array $constraints = []): self
    {
        return $this->register(HttpMethod::POST, $name, $path, $action, $constraints);
    }

    public function put(string $name, string $path, array|Closure $action, ?array $constraints = []): self
    {
        return $this->register(HttpMethod::PUT, $name, $path, $action, $constraints);
    }

    public function patch(string $name, string $path, array|Closure $action, ?array $constraints = []): self
    {
        return $this->register(HttpMethod::PATCH, $name, $path, $action, $constraints);
    }

    public function delete(string $name, string $path, array|Closure $action, ?array $constraints = []): self
    {
        return $this->register(HttpMethod::DELETE, $name, $path, $action, $constraints);
    }

    public function options(string $name, string $path, array|Closure $action, ?array $constraints = []): self
    {
        return $this->register(HttpMethod::OPTIONS, $name, $path, $action, $constraints);
    }

    private function register(
        HttpMethod $method,
        string $name,
        string $path,
        array|Closure $action,
        ?array $constraints = []
    ): self {
        $route = (new Route($path))
            ->setName($name)
            ->setMethod($method)
            ->setAction($action)
            ->setConstraints($constraints);

        if ($this->getRoute($route->getName()) !== null) {
            throw new DuplicateRouteException();
        }

        $this->routes[$name] = $route;

        return $this;
    }
}
