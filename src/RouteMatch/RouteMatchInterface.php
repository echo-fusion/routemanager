<?php

namespace EchoFusion\RouteManager\RouteMatch;

use EchoFusion\RouteManager\RouteInterface;

interface RouteMatchInterface
{
    public const REQUEST_KEY = 'routeMatch';

    public function setRoute(RouteInterface $route);

    public function getRoute(): RouteInterface;

    public function setParams(array $params): self;

    /**
     * @param non-empty-string $name
     */
    public function setParam(string $name, $value): self;

    public function getParams(): array;

    /**
     * @param non-empty-string $name
     */
    public function getParam(string $name): string|int|null;
}
