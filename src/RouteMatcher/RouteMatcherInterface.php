<?php

declare(strict_types=1);

namespace EchoFusion\RouteManager\RouteMatcher;

use EchoFusion\RouteManager\RouteInterface;
use EchoFusion\RouteManager\RouteMatch\RouteMatchInterface;
use Psr\Http\Message\ServerRequestInterface;

interface RouteMatcherInterface
{
    public function match(ServerRequestInterface $request, RouteInterface $route): ?RouteMatchInterface;
}
