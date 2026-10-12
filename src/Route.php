<?php

declare(strict_types=1);

namespace EchoFusion\RouteManager;

use Closure;
use EchoFusion\Contracts\RouteManager\RouteInterface;

/**
 * An immutable route definition. `with*()` methods return a changed copy.
 */
final class Route implements RouteInterface
{
    /**
     * @param non-empty-string $name
     * @param array<array-key, mixed>|Closure $action
     * @param array<string, non-empty-string>|null $constraints parameter name => regex
     * @param list<class-string> $middlewares
     */
    public function __construct(
        private readonly string $name,
        private readonly string $method,
        private readonly string $path,
        private readonly array|Closure $action,
        private readonly ?array $constraints = [],
        private readonly array $middlewares = [],
    ) {
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getPath(): string
    {
        return $this->path;
    }

    public function getAction(): array|Closure
    {
        return $this->action;
    }

    public function getConstraints(): ?array
    {
        return $this->constraints;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    /**
     * A copy of the route that also runs the given middleware (after the ones it already has).
     *
     * @param class-string ...$middlewares
     */
    public function withMiddlewares(string ...$middlewares): self
    {
        return new self(
            $this->name,
            $this->method,
            $this->path,
            $this->action,
            $this->constraints,
            [...$this->middlewares, ...$middlewares],
        );
    }
}
