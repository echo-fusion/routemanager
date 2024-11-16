<?php

declare(strict_types=1);

namespace EchoFusion\RouteManager;

use Closure;
use EchoFusion\Contracts\RouteManager\RouteInterface;

class Route implements RouteInterface
{
    protected string $method;

    protected string $name;

    protected string $path;

    protected array|Closure $action;

    protected ?array $middlewares = [];

    protected ?array $constraints = [];

    protected array $arguments = [];

    public function getArguments(): array
    {
        return $this->arguments;
    }

    public function __construct(string $path)
    {
        $this->path = $path;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function setMethod(string $method): self
    {
        $this->method = $method;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getAction(): array|Closure
    {
        return $this->action;
    }

    public function setAction(array|Closure $action): self
    {
        $this->action = $action;

        return $this;
    }

    public function getMiddlewares(): array
    {
        return $this->middlewares;
    }

    public function setMiddlewares(string ...$middlewares): self
    {
        foreach ($middlewares as $middleware) {
            $this->middlewares[] = $middleware;
        }

        return $this;
    }

    public function getConstraints(): ?array
    {
        return $this->constraints;
    }

    public function setConstraints(?array $constraints): self
    {
        $this->constraints = $constraints;

        return $this;
    }

    public function getPath(): string
    {
        return $this->path;
    }
}
