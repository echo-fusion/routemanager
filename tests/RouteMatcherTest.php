<?php

declare(strict_types=1);

use EchoFusion\RouteManager\HttpMethod;
use EchoFusion\RouteManager\RouteInterface;
use EchoFusion\RouteManager\RouteMatch\RouteMatch;
use EchoFusion\RouteManager\RouteMatcher\RouteMatcher;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;

class RouteMatcherTest extends TestCase
{
    public function testMatchReturnsNullForDifferentMethod(): void
    {
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->method('getMethod')->willReturn(HttpMethod::POST->value);

        $routeMock = $this->createMock(RouteInterface::class);
        $routeMock->method('getMethod')->willReturn(HttpMethod::GET);

        $routeMatcher = new RouteMatcher();
        $result = $routeMatcher->match($requestMock, $routeMock);

        $this->assertNull($result);
    }

    public function testMatchReturnsNullForDifferentPath(): void
    {
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->method('getMethod')->willReturn(HttpMethod::GET->value);

        $uriMock = $this->createMock(UriInterface::class);
        $uriMock->method('getPath')->willReturn('/different/path');
        $requestMock->method('getUri')->willReturn($uriMock);

        $routeMock = $this->createMock(RouteInterface::class);
        $routeMock->method('getMethod')->willReturn(HttpMethod::GET);
        $routeMock->method('getPath')->willReturn('/expected/path');

        $routeMatcher = new RouteMatcher();
        $result = $routeMatcher->match($requestMock, $routeMock);

        $this->assertNull($result);
    }

    public function testMatchReturnsRouteMatchOnSuccess(): void
    {
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->method('getMethod')->willReturn(HttpMethod::GET->value);

        $uriMock = $this->createMock(UriInterface::class);
        $uriMock->method('getPath')->willReturn('/users/123');
        $requestMock->method('getUri')->willReturn($uriMock);

        $routeMock = $this->createMock(RouteInterface::class);
        $routeMock->method('getMethod')->willReturn(HttpMethod::GET);
        $routeMock->method('getPath')->willReturn('/users/{id}');
        $routeMock->method('getConstraints')->willReturn(['id' => '\d+']);

        $routeMatcher = new RouteMatcher();
        $result = $routeMatcher->match($requestMock, $routeMock);

        $this->assertInstanceOf(RouteMatch::class, $result);
        $this->assertEquals(['id' => '123'], $result->getParams());
    }

    public function testMatchReturnsRouteMatchWithDefaultConstraints(): void
    {
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->method('getMethod')->willReturn(HttpMethod::GET->value);

        $uriMock = $this->createMock(UriInterface::class);
        $uriMock->method('getPath')->willReturn('/users/johndoe');
        $requestMock->method('getUri')->willReturn($uriMock);

        $routeMock = $this->createMock(RouteInterface::class);
        $routeMock->method('getMethod')->willReturn(HttpMethod::GET);
        $routeMock->method('getPath')->willReturn('/users/{username}');
        $routeMock->method('getConstraints')->willReturn(null);

        $routeMatcher = new RouteMatcher();
        $result = $routeMatcher->match($requestMock, $routeMock);

        $this->assertInstanceOf(RouteMatch::class, $result);
        $this->assertEquals(['username' => 'johndoe'], $result->getParams());
    }

    public function testMatchReturnsRouteMatchForAllHttpMethods(): void
    {
        foreach (HttpMethod::cases() as $httpMethod) {
            $requestMock = $this->createMock(ServerRequestInterface::class);
            $requestMock->method('getMethod')->willReturn($httpMethod->value);

            $uriMock = $this->createMock(UriInterface::class);
            $uriMock->method('getPath')->willReturn('/resource');
            $requestMock->method('getUri')->willReturn($uriMock);

            $routeMock = $this->createMock(RouteInterface::class);
            $routeMock->method('getMethod')->willReturn($httpMethod);
            $routeMock->method('getPath')->willReturn('/resource');

            $routeMatcher = new RouteMatcher();
            $result = $routeMatcher->match($requestMock, $routeMock);

            $this->assertInstanceOf(RouteMatch::class, $result);
        }
    }

    public function testMatchReturnsRouteMatchWithConstraints(): void
    {
        $requestMock = $this->createMock(ServerRequestInterface::class);
        $requestMock->method('getMethod')->willReturn(HttpMethod::GET->value);

        $uriMock = $this->createMock(UriInterface::class);
        $uriMock->method('getPath')->willReturn('/users/35/name/amir');
        $requestMock->method('getUri')->willReturn($uriMock);

        $routeMock = $this->createMock(RouteInterface::class);
        $routeMock->method('getMethod')->willReturn(HttpMethod::GET);
        $routeMock->method('getPath')->willReturn('/users/{id}/name/{name}');
        $routeMock->method('getConstraints')->willReturn(
            [
                'id' => '[0-9]+',
                'name' => '[a-zA-Z]+',
            ]
        );

        $routeMatcher = new RouteMatcher();
        $result = $routeMatcher->match($requestMock, $routeMock);

        $this->assertInstanceOf(RouteMatch::class, $result);
        $this->assertEquals(['id' => 35, 'name' => 'amir'], $result->getParams());
    }
}
