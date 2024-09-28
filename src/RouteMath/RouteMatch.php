<?php

declare(strict_types=1);

namespace EchoFusion\RouteManager\RouteMatch;

use EchoFusion\RouteManager\RouteInterface;
use function array_key_exists;

class RouteMatch implements RouteMatchInterface
{
    protected RouteInterface $route;

    protected array $params = [];

    protected array $body = [];

    public function setRoute(RouteInterface $route): self
    {
        $this->route = $route;

        return $this;
    }

    public function getRoute(): RouteInterface
    {
        return $this->route;
    }

    public function setParam(string $name, $value): self
    {
        $this->params[$name] = $value;

        return $this;
    }

    public function setParams(array $params): self
    {
        $this->params = $params;

        return $this;
    }

    public function getParams(): array
    {
        return $this->params;
    }

    public function getParam(string $name): ?string
    {
        if (array_key_exists($name, $this->params)) {
            return $this->params[$name];
        }

        return null;
    }

    public function getBody(): array
    {
        return $this->body;
    }

    public function setBody(array $body): self
    {
        $this->body = $body;

        return $this;
    }
}
