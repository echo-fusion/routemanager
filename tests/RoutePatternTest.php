<?php

declare(strict_types=1);

namespace EchoFusion\RouteManager\Tests;

use EchoFusion\RouteManager\Exceptions\InvalidRouteException;
use EchoFusion\RouteManager\RoutePattern;
use PHPUnit\Framework\TestCase;
use const ARRAY_FILTER_USE_KEY;
use function preg_match;

final class RoutePatternTest extends TestCase
{
    /**
     * @param array<string, string>|null $constraints
     *
     * @return array<string, string>|null the named parameters, or null when the path does not match
     */
    private function match(string $pattern, ?array $constraints, string $path): ?array
    {
        [$regex] = RoutePattern::compile($pattern, $constraints);

        if (preg_match($regex, $path, $m) !== 1) {
            return null;
        }

        return array_filter($m, 'is_string', ARRAY_FILTER_USE_KEY);
    }

    public function testStaticPaths(): void
    {
        $this->assertSame([], $this->match('/about', null, '/about'));
        $this->assertSame([], $this->match('/about', null, '/about/'), 'one trailing slash is tolerated');
        $this->assertNull($this->match('/about', null, '/about//'));
        $this->assertNull($this->match('/about', null, '/about-us'));
        $this->assertSame([], $this->match('/', null, '/'));
        $this->assertNull($this->match('/', null, '//'));
        $this->assertSame([], $this->match('/docs/', null, '/docs/'), 'a path that ends with a slash keeps it');
        $this->assertNull($this->match('/docs/', null, '/docs'));
    }

    public function testLiteralTextIsQuoted(): void
    {
        $this->assertNotNull($this->match('/file.json', null, '/file.json'));
        $this->assertNull($this->match('/file.json', null, '/fileXjson'));
        $this->assertNotNull($this->match('/a+b(c)', null, '/a+b(c)'));
        $this->assertNotNull($this->match('/100%/#top', null, '/100%/#top'), 'the regex delimiter in a path is safe');
    }

    public function testPlaceholderUsesTheDefaultConstraint(): void
    {
        $this->assertSame(['id' => '42'], $this->match('/post/{id}', null, '/post/42'));
        $this->assertSame(['id' => 'a-b_9'], $this->match('/post/{id}', null, '/post/a-b_9'));
        $this->assertNull($this->match('/post/{id}', null, '/post/a.b'));
        $this->assertNull($this->match('/post/{id}', null, '/post/'));
        $this->assertNull($this->match('/post/{id}', null, '/post/1/2'));
    }

    public function testConstraintsFromTheArrayAndInline(): void
    {
        $this->assertSame(['id' => '7'], $this->match('/u/{id}', ['id' => '[0-9]+'], '/u/7'));
        $this->assertNull($this->match('/u/{id}', ['id' => '[0-9]+'], '/u/abc'));

        $this->assertSame(['id' => '7'], $this->match('/u/{id:\d+}', null, '/u/7'));
        $this->assertNull($this->match('/u/{id:\d+}', null, '/u/abc'));
        $this->assertSame(['year' => '2024'], $this->match('/y/{year:\d{4}}', null, '/y/2024'), 'braces inside an inline constraint');
        $this->assertNull($this->match('/y/{year:\d{4}}', null, '/y/24'));
    }

    public function testTheConstraintsArrayWinsOverTheInlineOne(): void
    {
        $this->assertSame(['id' => 'abc'], $this->match('/u/{id:\d+}', ['id' => '[a-z]+'], '/u/abc'));
    }

    public function testSeveralParametersAndGroupsInsideConstraints(): void
    {
        $params = $this->match('/{lang}/post/{id}/{slug}', ['lang' => '(en|fa)', 'id' => '\d+', 'slug' => '[a-z-]+'], '/fa/post/9/hello-world');

        $this->assertSame(['lang' => 'fa', 'id' => '9', 'slug' => 'hello-world'], $params, 'a capturing group in a constraint must not shift the parameters');
    }

    public function testSpecialCharactersInConstraints(): void
    {
        $this->assertSame(['e' => 'a@b'], $this->match('/m/{e}', ['e' => '[a-z]+@[a-z]+'], '/m/a@b'), 'a "@" in a constraint');
        $this->assertSame(['t' => 'a#b'], $this->match('/h/{t}', ['t' => '[a-z]#[a-z]'], '/h/a#b'), 'the delimiter in a constraint');
    }

    public function testNonAsciiPaths(): void
    {
        $this->assertSame([], $this->match('/خانه', null, '/خانه'));
        $this->assertSame(['id' => '5'], $this->match('/مقاله/{id}/ویرایش', ['id' => '[0-9]+'], '/مقاله/5/ویرایش'));
        $this->assertSame(['slug' => 'abc'], $this->match('/ü/{slug:[a-z]+}/é', null, '/ü/abc/é'), 'text after a placeholder is not shifted');
        $this->assertNull($this->match('/مقاله/{id}', null, '/مقاله-x/5'));
    }

    public function testParameterNamesAreReturnedInOrder(): void
    {
        [, $names] = RoutePattern::compile('/{a}/{b:\d+}/{c}');

        $this->assertSame(['a', 'b', 'c'], $names);
    }

    /**
     * @dataProvider invalidPatterns
     *
     * @param array<string, string>|null $constraints
     */
    public function testInvalidPatterns(string $path, ?array $constraints, string $message): void
    {
        $this->expectException(InvalidRouteException::class);
        $this->expectExceptionMessage($message);

        RoutePattern::compile($path, $constraints);
    }

    /**
     * @return array<string, array{string, array<string, string>|null, string}>
     */
    public function invalidPatterns(): array
    {
        return [
            'unclosed brace' => ['/a/{id', null, 'unclosed'],
            'unclosed nested brace' => ['/a/{id:\d{3}', null, 'unclosed'],
            'bad name' => ['/a/{some-thing}', null, 'not a valid parameter name'],
            'name starting with a digit' => ['/a/{1st}', null, 'not a valid parameter name'],
            'empty name' => ['/a/{}', null, 'not a valid parameter name'],
            'duplicate name' => ['/a/{id}/{id}', null, 'used twice'],
            'empty constraint' => ['/a/{id:}', null, 'is empty'],
            'broken constraint' => ['/a/{id}', ['id' => '[a-z'], 'not a valid pattern'],
        ];
    }
}
