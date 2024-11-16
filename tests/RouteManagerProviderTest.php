<?php

declare(strict_types=1);

namespace EchoFusion\RouteManager\Tests;

use Closure;
use EchoFusion\Contracts\RouteManager\RouteMatcherInterface;
use EchoFusion\Contracts\RouteManager\RouterInterface;
use EchoFusion\Contracts\ServiceManager\SettableContainerInterface;
use EchoFusion\RouteManager\Exceptions\DuplicateRouteException;
use EchoFusion\RouteManager\Providers\RouteManagerProvider;
use EchoFusion\RouteManager\RouteMatcher;
use EchoFusion\RouteManager\Router;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerExceptionInterface;

class RouteManagerProviderTest extends TestCase
{
    private SettableContainerInterface|MockObject $container;

    private RouteManagerProvider $provider;

    protected function setUp(): void
    {
        $this->container = $this->createMock(SettableContainerInterface::class);
        $this->provider = new RouteManagerProvider();
    }

    public function testRegister(): void
    {
        $this->container->expects($this->exactly(2))
            ->method('set')
            ->willReturnCallback(function ($interface, $closure) {
                if ($interface === RouteMatcherInterface::class) {
                    $instance = $closure();
                    $this->assertInstanceOf(RouteMatcher::class, $instance);
                } elseif ($interface === RouterInterface::class) {
                    $this->container->method('get')
                        ->with(RouteMatcherInterface::class)
                        ->willReturn(new RouteMatcher());
                    $instance = $closure();
                    $this->assertInstanceOf(Router::class, $instance);
                }

                return true;
            });

        $this->provider->register($this->container);
    }

    public function testBootWithProvidedConfig(): void
    {
        $config = [
            'health_check' => [
                'method' => 'GET',
                'path' => '/health-check',
                'action' => fn () => 'healthy',
                'constraints' => [],
                'middlewares' => [],
            ],
        ];

        $routerMock = $this->createMock(RouterInterface::class);
        $routerMock->expects($this->once())
            ->method('fromArray')
            ->with($config);

        $this->container->method('get')
            ->willReturnMap([
                [RouterInterface::class, $routerMock],
            ]);

        $this->container->expects($this->once())
            ->method('set')
            ->with(RouterInterface::class, $this->isInstanceOf(Closure::class));

        $this->provider->boot($this->container, $config);
    }

    public function testBootWithDefaultConfig(): void
    {
        $defaultConfig = [
            'home' => [
                'method' => 'GET',
                'path' => '/',
                'action' => fn () => 'home',
                'constraints' => [],
                'middlewares' => [],
            ],
        ];

        $routerMock = $this->createMock(RouterInterface::class);

        $routerMock->expects($this->once())
            ->method('fromArray')
            ->with($defaultConfig);

        $this->container
            ->method('get')
            ->willReturnMap([
                [RouterInterface::class, $routerMock],
            ]);

        $this->container->expects($this->once())
            ->method('set')
            ->with(RouterInterface::class, $this->isInstanceOf(Closure::class));

        $this->provider->boot($this->container, $defaultConfig);
    }

    public function testBootThrowsDuplicateRouteException(): void
    {
        $this->expectException(DuplicateRouteException::class);

        $routerMock = $this->createMock(RouterInterface::class);
        $routerMock->method('fromArray')
            ->willThrowException(new DuplicateRouteException('Route already exists'));

        $this->container->method('get')
            ->willReturnMap([
                [RouterInterface::class, $routerMock],
            ]);

        $this->provider->boot($this->container, ['some_route' => []]);
    }

    public function testBootHandlesContainerExceptions(): void
    {
        $this->expectException(ContainerExceptionInterface::class);

        $this->container->method('get')
            ->willThrowException($this->createMock(ContainerExceptionInterface::class));

        $this->provider->boot($this->container);
    }
}
