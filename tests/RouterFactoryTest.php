<?php

declare(strict_types=1);

use EchoFusion\RouteManager\RouteMatch\RouteMatcherInterface;
use EchoFusion\RouteManager\RouterFactory;
use EchoFusion\RouteManager\RouterInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;

class RouterFactoryTest extends TestCase
{
    private RouterFactory $routerFactory;

    protected function setUp(): void
    {
        $this->routerFactory = new RouterFactory();
    }

    public function testInvokeReturnsRouterInstance(): void
    {
        // Create a mock for RouteMatcherInterface
        $routeMatcherMock = $this->createMock(RouteMatcherInterface::class);

        // Create a mock for ContainerInterface
        $containerMock = $this->createMock(ContainerInterface::class);
        $containerMock->method('get')
            ->with(RouteMatcherInterface::class)
            ->willReturn($routeMatcherMock);

        // Invoke the factory
        $router = ($this->routerFactory)($containerMock);

        // Assert that the returned object is an instance of RouterInterface
        $this->assertInstanceOf(RouterInterface::class, $router);

        // Assert that the router was created with the correct RouteMatcher
//        $this->assertSame($routeMatcherMock, $router->getRouteMatcher());
    }
}
