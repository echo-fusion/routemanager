<?php

declare(strict_types=1);

namespace EchoFusion\RouteManager\RouteMatcher;

use EchoFusion\RouteManager\RouteInterface;
use EchoFusion\RouteManager\RouteMatch\RouteMatch;
use EchoFusion\RouteManager\RouteMatch\RouteMatchInterface;
use Psr\Http\Message\ServerRequestInterface;

class RouteMatcher implements RouteMatcherInterface
{
    public function match(ServerRequestInterface $request, RouteInterface $route): ?RouteMatchInterface
    {
        $requestMethod = $request->getMethod();
        $requestedPath = $request->getUri()->getPath();

        $path = $route->getPath();

        if (mb_strtolower($route->getMethod()->value) !== mb_strtolower($requestMethod)) {
            return null;
        }

        $constraints = $route->getConstraints() ?? [];

        $routeRegex = preg_replace_callback('/{([^}]+)}/', function ($matches) use ($constraints) {
            $paramName = $matches[1];

            return isset($constraints[$paramName]) ? '(' . $constraints[$paramName] . ')' : '([a-zA-Z0-9_-]+)';
        }, $path);

        $routeRegex = '@^' . $routeRegex . '$@';

        if (preg_match($routeRegex, $requestedPath, $matches)) {
            array_shift($matches);

            $routeParamsNames = [];
            if (preg_match_all('/{(\w+)(:[^}]+)?}/', $path, $paramMatches)) {
                $routeParamsNames = $paramMatches[1];
            }

            $routeParams = array_combine($routeParamsNames, $matches);

            return (new RouteMatch())
                ->setRoute($route)
                ->setParams($routeParams);
        }

        return null;
    }
}
