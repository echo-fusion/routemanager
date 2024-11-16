<?php

declare(strict_types=1);

namespace EchoFusion\RouteManager\Tests;

use EchoFusion\Contracts\RouteManager\RouteMatcherInterface;
use EchoFusion\Contracts\RouteManager\RouteMatchInterface;
use EchoFusion\Contracts\RouteManager\RouterInterface;
use EchoFusion\RouteManager\Exceptions\DuplicateRouteException;
use EchoFusion\RouteManager\Exceptions\RouteNotFoundException;
use EchoFusion\RouteManager\Route;
use EchoFusion\RouteManager\Router;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

class RouterTest extends TestCase
{
    private RouterInterface $router;

    private MockObject|RouteMatcherInterface $routeMatcher;

    protected function setUp(): void
    {
        $this->routeMatcher = $this->createMock(RouteMatcherInterface::class);
        $this->router = new Router($this->routeMatcher);
    }

    public function testDispatchThrowsRouteNotFoundException(): void
    {
        $this->expectException(RouteNotFoundException::class);
        $request = $this->createMock(ServerRequestInterface::class);
        $this->router->dispatch($request);
    }

    public function testDispatchReturnsRouteMatch(): void
    {
        $route = (new Route('/test'))
            ->setName('test')
            ->setMethod('get')
            ->setAction(fn () => 'test action');

        $this->router->get($route->getName(), $route->getPath(), $route->getAction());

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('get');
        $uri = $this->createMock(UriInterface::class);
        $request->method('getUri')->willReturn($uri);

        $this->routeMatcher
            ->method('match')
            ->with($request, $route)
            ->willReturn($this->createMock(RouteMatchInterface::class));

        $routeMatch = $this->router->dispatch($request);
        $this->assertInstanceOf(RouteMatchInterface::class, $routeMatch);
    }

    public function testAddRouteSuccess(): void
    {
        $this->router->get('testRoute', '/test', function () {
            return 'Hello, World!';
        });

        $routes = $this->router->getRoutes();
        $this->assertCount(1, $routes);
        $this->assertArrayHasKey('testRoute', $routes);
    }

    public function testAddRouteThrowsDuplicateRouteException(): void
    {
        $this->expectException(DuplicateRouteException::class);

        $this->router->get('duplicateRoute', '/duplicate', function () {
            return 'First Action';
        });
        $this->router->get('duplicateRoute', '/duplicate', function () {
            return 'Second Action';
        });
    }

    public function testGetRouteReturnsRegisteredRoute(): void
    {
        $this->router->get('existingRoute', '/existing', function () {
            return 'Existing Route Action';
        });

        $route = $this->router->getRoute('existingRoute');
        $this->assertInstanceOf(Route::class, $route);
    }

    public function testGetRouteReturnsNullForNonExistentRoute(): void
    {
        $route = $this->router->getRoute('nonExistentRoute');
        $this->assertNull($route);
    }

    public function testDispatchHandlesMethodNotAllowed(): void
    {
        $this->expectException(RouteNotFoundException::class);

        $route = (new Route('/method'))
            ->setName('methodRoute')
            ->setMethod('post')
            ->setAction(fn () => 'Method action');

        $this->router->post($route->getName(), $route->getPath(), $route->getAction());

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('get');
        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/method');
        $request->method('getUri')->willReturn($uri);

        $this->router->dispatch($request);
    }

    public function testDynamicRouteParameters(): void
    {
        $route = (new Route('/user/{id}'))
            ->setName('userRoute')
            ->setMethod('get')
            ->setAction(fn () => 'User action');

        $this->router->get($route->getName(), $route->getPath(), $route->getAction());

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('get');
        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/user/123');
        $request->method('getUri')->willReturn($uri);

        $this->routeMatcher
            ->method('match')
            ->with($request, $route)
            ->willReturn($this->createMock(RouteMatchInterface::class));

        $routeMatch = $this->router->dispatch($request);
        $this->assertInstanceOf(RouteMatchInterface::class, $routeMatch);
    }

    public function testOptionsRouteSuccess(): void
    {
        $route = (new Route('/options'))
            ->setName('optionsRoute')
            ->setMethod('options')
            ->setAction(fn () => 'Options action');

        $this->router->options($route->getName(), $route->getPath(), $route->getAction());

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('options');
        $uri = $this->createMock(UriInterface::class);
        $request->method('getUri')->willReturn($uri);
        $uri->method('getPath')->willReturn('/options');

        $this->routeMatcher
            ->method('match')
            ->with($request, $route)
            ->willReturn($this->createMock(RouteMatchInterface::class));

        $routeMatch = $this->router->dispatch($request);
        $this->assertInstanceOf(RouteMatchInterface::class, $routeMatch);
    }

    public function testDeleteRouteSuccess(): void
    {
        $route = (new Route('/delete'))
            ->setName('deleteRoute')
            ->setMethod('delete')
            ->setAction(fn () => 'Delete action');

        $this->router->delete($route->getName(), $route->getPath(), $route->getAction());

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('delete');
        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/delete');
        $request->method('getUri')->willReturn($uri);

        $this->routeMatcher
            ->method('match')
            ->with($request, $route)
            ->willReturn($this->createMock(RouteMatchInterface::class));

        $routeMatch = $this->router->dispatch($request);
        $this->assertInstanceOf(RouteMatchInterface::class, $routeMatch);
    }

    public function testPutRouteSuccess(): void
    {
        $route = (new Route('/put'))
            ->setName('putRoute')
            ->setMethod('put')
            ->setAction(fn () => 'Put action');

        $this->router->put($route->getName(), $route->getPath(), $route->getAction());

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('put');
        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/put');
        $request->method('getUri')->willReturn($uri);

        $this->routeMatcher
            ->method('match')
            ->with($request, $route)
            ->willReturn($this->createMock(RouteMatchInterface::class));

        $routeMatch = $this->router->dispatch($request);
        $this->assertInstanceOf(RouteMatchInterface::class, $routeMatch);
    }

    public function testPatchRouteSuccess(): void
    {
        $route = (new Route('/patch'))
            ->setName('patchRoute')
            ->setMethod('patch')
            ->setAction(fn () => 'Patch action');

        $this->router->patch($route->getName(), $route->getPath(), $route->getAction());

        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn('patch');
        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn('/patch');
        $request->method('getUri')->willReturn($uri);

        $this->routeMatcher
            ->method('match')
            ->with($request, $route)
            ->willReturn($this->createMock(RouteMatchInterface::class));

        $routeMatch = $this->router->dispatch($request);
        $this->assertInstanceOf(RouteMatchInterface::class, $routeMatch);
    }

    public function testFromArrayRegistersRoutesCorrectly(): void
    {
        $routes = [
            'health_check' => [
                'method' => 'GET',
                'path' => '/health-check',
                'action' => fn () => 'healthy',
                'constraints' => [],
                'middlewares' => [],
            ],
        ];

        $this->router->fromArray($routes);

        $registeredRoutes = $this->router->getRoutes();
        $this->assertArrayHasKey('health_check', $registeredRoutes);
        $this->assertEquals('GET', $registeredRoutes['health_check']->getMethod());
        $this->assertEquals('/health-check', $registeredRoutes['health_check']->getPath());
        $this->assertIsCallable($registeredRoutes['health_check']->getAction());
        $this->assertEquals([], $registeredRoutes['health_check']->getConstraints());
        $this->assertEquals([], $registeredRoutes['health_check']->getMiddlewares());
    }
}
