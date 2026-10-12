<?php

declare(strict_types=1);

namespace EchoFusion\RouteManager;

use EchoFusion\RouteManager\Exceptions\InvalidRouteException;
use function in_array;
use function preg_match;
use function preg_quote;
use function preg_replace;
use function sprintf;
use function strlen;
use function strpos;
use function substr;

/**
 * Turns a route path such as `/post/{id}/{slug:[a-z-]+}` into one anchored regular expression.
 *
 * - Literal text is quoted, so `/file.json` only matches a real dot.
 * - A placeholder is `{name}` or `{name:regex}`; a `constraints` entry for the name wins over an inline regex,
 *   and a placeholder without either matches `[a-zA-Z0-9_-]+`.
 * - Placeholders become named groups, so a constraint may contain its own groups without shifting the parameters.
 *
 * @internal
 */
final class RoutePattern
{
    private const DEFAULT_CONSTRAINT = '[a-zA-Z0-9_-]+';

    /**
     * @param array<string, string>|null $constraints parameter name => regex
     *
     * @return array{0: string, 1: list<string>} the regex and the parameter names in order
     *
     * @throws InvalidRouteException
     */
    public static function compile(string $path, ?array $constraints = null): array
    {
        $constraints ??= [];
        $regex = '';
        $names = [];
        $length = strlen($path);
        $literalStart = 0;

        for ($i = 0; $i < $length; $i++) {
            if ($path[$i] !== '{') {
                continue;
            }

            $regex .= preg_quote(substr($path, $literalStart, $i - $literalStart), '#');

            // find the closing brace, counting nested braces so `{id:\d{3}}` works
            $depth = 1;
            $end = $i + 1;
            while ($end < $length && $depth > 0) {
                $depth += $path[$end] === '{' ? 1 : ($path[$end] === '}' ? -1 : 0);
                $end++;
            }
            if ($depth !== 0) {
                throw new InvalidRouteException(sprintf('The route path "%s" has an unclosed "{".', $path));
            }

            $placeholder = substr($path, $i + 1, $end - $i - 2);
            $colon = strpos($placeholder, ':');
            $name = $colon === false ? $placeholder : substr($placeholder, 0, $colon);
            $inline = $colon === false ? null : substr($placeholder, $colon + 1);

            if (preg_match('/^\w+$/', $name) !== 1 || preg_match('/^\d/', $name) === 1) {
                throw new InvalidRouteException(sprintf('"%s" is not a valid parameter name in the route path "%s".', $name, $path));
            }
            if (in_array($name, $names, true)) {
                throw new InvalidRouteException(sprintf('The parameter "{%s}" is used twice in the route path "%s".', $name, $path));
            }

            $pattern = $constraints[$name] ?? $inline ?? self::DEFAULT_CONSTRAINT;
            if ($pattern === '') {
                throw new InvalidRouteException(sprintf('The constraint of parameter "%s" in "%s" is empty.', $name, $path));
            }

            $names[] = $name;
            // "#" is the delimiter; an unescaped one inside a user pattern must not end the regex
            $regex .= sprintf('(?P<%s>%s)', $name, (string) preg_replace('/(?<!\\\\)#/', '\\#', $pattern));

            $i = $end - 1;
            $literalStart = $end;
        }

        $regex .= preg_quote(substr($path, $literalStart), '#');

        $compiled = sprintf('#^%s$#D', $regex);
        if (@preg_match($compiled, '') === false) {
            throw new InvalidRouteException(sprintf('The route path "%s" with its constraints is not a valid pattern.', $path));
        }

        return [$compiled, $names];
    }
}
