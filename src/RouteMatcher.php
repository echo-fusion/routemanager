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

class RouteMatcher implements RouteMatcherInterface
{
    /**
     * Compiled regexes by path and constraints, so a route is compiled once instead of on every request.
     *
     * @var array<string, string>
     */
    private array $compiled = [];

    public function match(ServerRequestInterface $request, RouteInterface $route): ?RouteMatchInterface
    {
        if (strcasecmp($route->getMethod(), $request->getMethod()) !== 0) {
            return null;
        }

        $regex = $this->regex($route);

        if (preg_match($regex, $request->getUri()->getPath(), $matches) !== 1) {
            return null;
        }

        // named groups only: the numeric entries are duplicates
        $params = array_filter($matches, static fn ($key): bool => is_string($key), ARRAY_FILTER_USE_KEY);

        return (new RouteMatch())
            ->setRoute($route)
            ->setParams($params);
    }

    private function regex(RouteInterface $route): string
    {
        $constraints = $route->getConstraints();
        $key = $route->getPath() . "\0" . json_encode($constraints);

        return $this->compiled[$key] ??= RoutePattern::compile($route->getPath(), $constraints)[0];
    }
}
