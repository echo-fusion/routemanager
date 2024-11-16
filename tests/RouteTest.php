<?php

declare(strict_types=1);

namespace EchoFusion\RouteManager\Tests;

use Closure;
use EchoFusion\RouteManager\Route;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class RouteTest extends TestCase
{
    public function testConstructorSetsPath(): void
    {
        $route = new Route('/test-path');
        $this->assertSame('/test-path', $route->getPath());
    }

    public function testSetAndGetMethod(): void
    {
        $route = new Route('/test');
        $route->setMethod('get');

        $this->assertSame('get', $route->getMethod());
    }

    public function testSetAndGetName(): void
    {
        $route = new Route('/test');
        $route->setName('test-route');

        $this->assertSame('test-route', $route->getName());
    }

    public function testSetAndGetAction(): void
    {
        $route = new Route('/test');

        $actionArray = ['Controller', 'method'];
        $route->setAction($actionArray);
        $this->assertSame($actionArray, $route->getAction());

        $actionClosure = function () {
            return 'closure action';
        };
        $route->setAction($actionClosure);
        $this->assertInstanceOf(Closure::class, $route->getAction());
    }

    public function testSetAndGetMiddlewares(): void
    {
        $route = new Route('/test');

        $this->assertEmpty($route->getMiddlewares());

        $existingMiddleware = FakeMiddleware::class;
        $route->setMiddlewares($existingMiddleware);
        $this->assertSame([$existingMiddleware], $route->getMiddlewares());
    }

    public function testSetAndGetConstraints(): void
    {
        $route = new Route('/test');
        $constraints = ['id' => '\d+'];
        $route->setConstraints($constraints);

        $this->assertSame($constraints, $route->getConstraints());
    }

    public function testSetAndGetArguments(): void
    {
        $route = new Route('/test');
        $arguments = ['id' => 123, 'slug' => 'test-slug'];

        $reflection = new ReflectionClass(Route::class);
        $property = $reflection->getProperty('arguments');
        $property->setAccessible(true);
        $property->setValue($route, $arguments);

        $this->assertSame($arguments, $route->getArguments());
    }
}

// Define a mock middleware class for the test case
class FakeMiddleware
{
}
