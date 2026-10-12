<?php

declare(strict_types=1);

namespace EchoFusion\RouteManager\Tests;

use Closure;
use EchoFusion\Contracts\RouteManager\RouteInterface;
use EchoFusion\RouteManager\Route;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class RouteTest extends TestCase
{
    public function testItIsCreatedComplete(): void
    {
        $action = fn () => 'x';
        $route = new Route('blog.show', 'get', '/post/{id}', $action, ['id' => '[0-9]+'], ['AuthMiddleware']);

        $this->assertInstanceOf(RouteInterface::class, $route);
        $this->assertSame('blog.show', $route->getName());
        $this->assertSame('get', $route->getMethod());
        $this->assertSame('/post/{id}', $route->getPath());
        $this->assertSame($action, $route->getAction());
        $this->assertSame(['id' => '[0-9]+'], $route->getConstraints());
        $this->assertSame(['AuthMiddleware'], $route->getMiddlewares());
    }

    public function testOptionalPartsDefaultToNothing(): void
    {
        $route = new Route('home', 'get', '/', [self::class, 'method']);

        $this->assertSame([], $route->getConstraints());
        $this->assertSame([], $route->getMiddlewares());
        $this->assertSame([self::class, 'method'], $route->getAction());
    }

    public function testActionCanBeAClosureOrAnArray(): void
    {
        $this->assertInstanceOf(Closure::class, (new Route('a', 'get', '/', fn () => null))->getAction());
        $this->assertIsArray((new Route('b', 'get', '/', ['Controller', 'index']))->getAction());
    }

    public function testWithMiddlewaresReturnsAChangedCopy(): void
    {
        $route = new Route('r', 'get', '/', fn () => null, ['id' => '\\d+'], ['First']);
        $copy = $route->withMiddlewares('Second', 'Third');

        $this->assertNotSame($route, $copy);
        $this->assertSame(['First'], $route->getMiddlewares(), 'the original does not change');
        $this->assertSame(['First', 'Second', 'Third'], $copy->getMiddlewares());
        $this->assertSame(['id' => '\\d+'], $copy->getConstraints());
        $this->assertSame($route->getAction(), $copy->getAction());
        $this->assertSame('r', $copy->getName());
    }

    public function testThereAreNoSetters(): void
    {
        $setters = array_filter(
            array_map(static fn ($m) => $m->getName(), (new ReflectionClass(Route::class))->getMethods()),
            static fn (string $name): bool => str_starts_with($name, 'set'),
        );

        $this->assertSame([], array_values($setters));
        $this->assertTrue((new ReflectionClass(Route::class))->isFinal());
    }
}
