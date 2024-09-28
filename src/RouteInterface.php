<?php

declare(strict_types=1);

namespace EchoFusion\RouteManager;

use Closure;

interface RouteInterface
{
    public function getMethod(): HttpMethod;

    public function setMethod(HttpMethod $method): self;

    public function getName(): string;

    public function setName(string $name): self;

    public function getAction(): array|Closure;

    public function setAction(array|Closure $action): self;

    public function getMiddlewares(): array;

    public function setMiddlewares(string ...$middlewares): self;

    public function getConstraints(): ?array;

    public function setConstraints(?array $constraints): self;

    public function getPath(): string;
}
