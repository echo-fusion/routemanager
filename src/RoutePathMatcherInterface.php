<?php

declare(strict_types=1);

namespace EchoFusion\RouteManager;

use EchoFusion\Contracts\RouteManager\RouteInterface;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Optional capability of a route matcher: tell whether a route's path matches regardless of the method.
 * The router uses it to answer "405 Method Not Allowed" instead of "404 Not Found".
 */
interface RoutePathMatcherInterface
{
    public function matchesPath(ServerRequestInterface $request, RouteInterface $route): bool;
}
