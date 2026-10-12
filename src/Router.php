<?php

declare(strict_types=1);

namespace EchoFusion\RouteManager;

use Closure;
use EchoFusion\Contracts\RouteManager\RouteLoaderInterface;
use EchoFusion\Contracts\RouteManager\RouteMatcherInterface;
use EchoFusion\Contracts\RouteManager\RouteMatchInterface;
use EchoFusion\Contracts\RouteManager\RouterInterface;
use EchoFusion\RouteManager\Exceptions\DuplicateRouteException;
use EchoFusion\RouteManager\Exceptions\InvalidRouteException;
use EchoFusion\RouteManager\Exceptions\RouteNotFoundException;
use Psr\Http\Message\ServerRequestInterface;
use function sprintf;

class Router implements RouterInterface, RouteLoaderInterface
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

        throw RouteNotFoundException::forRequest($request->getMethod(), $request->getUri()->getPath());
    }

    public function get(string $name, string $path, array|Closure $action, ?array $constraints = []): self
    {
        return $this->register('get', $name, $path, $action, $constraints);
    }

    public function post(string $name, string $path, array|Closure $action, ?array $constraints = []): self
    {
        return $this->register('post', $name, $path, $action, $constraints);
    }

    public function put(string $name, string $path, array|Closure $action, ?array $constraints = []): self
    {
        return $this->register('put', $name, $path, $action, $constraints);
    }

    public function patch(string $name, string $path, array|Closure $action, ?array $constraints = []): self
    {
        return $this->register('patch', $name, $path, $action, $constraints);
    }

    public function delete(string $name, string $path, array|Closure $action, ?array $constraints = []): self
    {
        return $this->register('delete', $name, $path, $action, $constraints);
    }

    public function options(string $name, string $path, array|Closure $action, ?array $constraints = []): self
    {
        return $this->register('options', $name, $path, $action, $constraints);
    }

    private function register(
        string $method,
        string $name,
        string $path,
        array|Closure $action,
        ?array $constraints = [],
        ?array $middlewares = [],
    ): self {
        $route = (new Route($path))
            ->setName($name)
            ->setMethod($method)
            ->setAction($action)
            ->setConstraints($constraints)
            ->setMiddlewares(...$middlewares);

        if ($this->getRoute($route->getName()) !== null) {
            throw DuplicateRouteException::forName($route->getName());
        }

        // fail now, with the route's name, instead of on the first request
        RoutePattern::compile($path, $constraints);

        $this->routes[$name] = $route;

        return $this;
    }

    public function fromArray(array $routes): void
    {
        foreach ($routes as $name => $route) {
            foreach (['method', 'path', 'action'] as $required) {
                if (!isset($route[$required])) {
                    throw new InvalidRouteException(sprintf('The route "%s" needs a "%s".', $name, $required));
                }
            }

            $this->register(
                $route['method'],
                (string) $name,
                $route['path'],
                $route['action'],
                $route['constraints'] ?? [],
                $route['middlewares'] ?? [],
            );
        }
    }
}
