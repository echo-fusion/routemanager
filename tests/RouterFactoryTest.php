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
        $routeMatcherMock = $this->createMock(RouteMatcherInterface::class);

        $containerMock = $this->createMock(ContainerInterface::class);
        $containerMock->method('get')
            ->with(RouteMatcherInterface::class)
            ->willReturn($routeMatcherMock);

        // Invoke the factory
        $router = ($this->routerFactory)($containerMock);

        $this->assertInstanceOf(RouterInterface::class, $router);
    }
}
