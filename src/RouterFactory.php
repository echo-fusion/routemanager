<?php

declare(strict_types=1);

namespace EchoFusion\RouteManager;

use EchoFusion\RouteManager\RouteMatch\RouteMatcherInterface;
use PHPUnit\Framework\Assert;
use Psr\Container\ContainerInterface;

class RouterFactory
{
    public function __invoke(ContainerInterface $container): RouterInterface
    {
        $RouteMatcher = $container->get(RouteMatcherInterface::class);
        Assert::assertInstanceOf($RouteMatcher, RouteMatcherInterface::class);

        return new Router($RouteMatcher);
    }
}
