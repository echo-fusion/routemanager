<?php

declare(strict_types=1);

namespace EchoFusion\RouteManager\Tests;

use EchoFusion\RouteManager\Exceptions\DuplicateRouteException;
use EchoFusion\RouteManager\Exceptions\InvalidRouteException;
use EchoFusion\RouteManager\Exceptions\RouteNotFoundException;
use EchoFusion\RouteManager\RouteMatcher;
use EchoFusion\RouteManager\Router;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\UriInterface;
use function sprintf;

/**
 * The real router and matcher working together.
 */
final class RoutingTest extends TestCase
{
    private function router(): Router
    {
        return new Router(new RouteMatcher());
    }

    private function request(string $method, string $path): ServerRequestInterface
    {
        $uri = $this->createMock(UriInterface::class);
        $uri->method('getPath')->willReturn($path);
        $request = $this->createMock(ServerRequestInterface::class);
        $request->method('getMethod')->willReturn($method);
        $request->method('getUri')->willReturn($uri);

        return $request;
    }

    public function testParametersAreReturnedByName(): void
    {
        $router = $this->router();
        $router->get('post', '/post/{id}/{slug}', fn () => null, ['id' => '[0-9]+']);

        $match = $router->dispatch($this->request('GET', '/post/12/hello-world'));

        $this->assertSame(['id' => '12', 'slug' => 'hello-world'], $match->getParams());
        $this->assertSame('post', $match->getRoute()->getName());
    }

    public function testTheMethodIsComparedCaseInsensitively(): void
    {
        $router = $this->router();
        $router->post('save', '/save', fn () => null);

        $this->assertSame('save', $router->dispatch($this->request('POST', '/save'))->getRoute()->getName());
        $this->assertSame('save', $router->dispatch($this->request('post', '/save'))->getRoute()->getName());
    }

    public function testADotInThePathIsLiteral(): void
    {
        $router = $this->router();
        $router->get('json', '/file.json', fn () => null);

        $this->assertSame('json', $router->dispatch($this->request('GET', '/file.json'))->getRoute()->getName());

        $this->expectException(RouteNotFoundException::class);
        $router->dispatch($this->request('GET', '/fileXjson'));
    }

    public function testInlineConstraintsAreHonoured(): void
    {
        $router = $this->router();
        $router->get('user', '/u/{id:\d+}', fn () => null);

        $this->assertSame(['id' => '5'], $router->dispatch($this->request('GET', '/u/5'))->getParams());

        $this->expectException(RouteNotFoundException::class);
        $router->dispatch($this->request('GET', '/u/abc'));
    }

    public function testNotFoundExplainsWhatWasRequested(): void
    {
        $this->expectException(RouteNotFoundException::class);
        $this->expectExceptionMessage('No route matches GET /missing.');

        $this->router()->dispatch($this->request('get', '/missing'));
    }

    public function testDuplicateNamesAreReportedWithTheName(): void
    {
        $router = $this->router();
        $router->get('home', '/', fn () => null);

        $this->expectException(DuplicateRouteException::class);
        $this->expectExceptionMessage('A route named "home" is already registered.');
        $router->get('home', '/other', fn () => null);
    }

    public function testAnInvalidPathFailsWhenTheRouteIsRegistered(): void
    {
        $this->expectException(InvalidRouteException::class);
        $this->expectExceptionMessage('unclosed');

        $this->router()->get('broken', '/a/{id', fn () => null);
    }

    public function testFromArrayAcceptsRoutesWithoutOptionalKeys(): void
    {
        $router = $this->router();
        $router->fromArray([
            'home' => ['method' => 'GET', 'path' => '/', 'action' => fn () => 'home'],
            'show' => ['method' => 'GET', 'path' => '/p/{id}', 'action' => fn () => 'show', 'constraints' => ['id' => '\d+'], 'middlewares' => ['AuthMiddleware']],
        ]);

        $this->assertSame('home', $router->dispatch($this->request('GET', '/'))->getRoute()->getName());
        $this->assertSame(['AuthMiddleware'], $router->dispatch($this->request('GET', '/p/3'))->getRoute()->getMiddlewares());
    }

    /**
     * @dataProvider incompleteRoutes
     *
     * @param array<string, mixed> $definition
     */
    public function testFromArrayNamesTheMissingKey(array $definition, string $key): void
    {
        $this->expectException(InvalidRouteException::class);
        $this->expectExceptionMessage(sprintf('The route "broken" needs a "%s".', $key));

        $this->router()->fromArray(['broken' => $definition]);
    }

    /**
     * @return array<string, array{array<string, mixed>, string}>
     */
    public function incompleteRoutes(): array
    {
        $action = fn () => null;

        return [
            'no method' => [['path' => '/', 'action' => $action], 'method'],
            'no path' => [['method' => 'GET', 'action' => $action], 'path'],
            'no action' => [['method' => 'GET', 'path' => '/'], 'action'],
        ];
    }
}
