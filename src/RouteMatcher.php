<?php

declare(strict_types=1);

namespace EchoFusion\RouteManager;

use EchoFusion\Contracts\RouteManager\RouteInterface;
use EchoFusion\Contracts\RouteManager\RouteMatcherInterface;
use EchoFusion\Contracts\RouteManager\RouteMatchInterface;
use Psr\Http\Message\ServerRequestInterface;
use const ARRAY_FILTER_USE_KEY;
use function array_filter;
use function is_string;
use function json_encode;
use function preg_match;
use function strcasecmp;

class RouteMatcher implements RouteMatcherInterface, RoutePathMatcherInterface
{
    /**
     * Compiled regexes by path and constraints, so a route is compiled once instead of on every request.
     *
     * @var array<string, string>
     */
    private array $compiled = [];

    public function match(ServerRequestInterface $request, RouteInterface $route): ?RouteMatchInterface
    {
        if (!$this->methodMatches($request, $route)) {
            return null;
        }

        if (preg_match($this->regex($route), $request->getUri()->getPath(), $matches) !== 1) {
            return null;
        }

        // named groups only: the numeric entries are duplicates
        $params = array_filter($matches, static fn ($key): bool => is_string($key), ARRAY_FILTER_USE_KEY);

        return (new RouteMatch())
            ->setRoute($route)
            ->setParams($params);
    }

    public function matchesPath(ServerRequestInterface $request, RouteInterface $route): bool
    {
        return preg_match($this->regex($route), $request->getUri()->getPath()) === 1;
    }

    /**
     * Methods are compared case-insensitively, and a HEAD request is served by the GET route of the same path.
     */
    private function methodMatches(ServerRequestInterface $request, RouteInterface $route): bool
    {
        $requested = $request->getMethod();

        return strcasecmp($route->getMethod(), $requested) === 0
            || (strcasecmp($requested, 'HEAD') === 0 && strcasecmp($route->getMethod(), 'GET') === 0);
    }

    private function regex(RouteInterface $route): string
    {
        $constraints = $route->getConstraints();
        $key = $route->getPath() . "\0" . json_encode($constraints);

        return $this->compiled[$key] ??= RoutePattern::compile($route->getPath(), $constraints)[0];
    }
}
