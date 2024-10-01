<?php

declare(strict_types=1);

use EchoFusion\RouteManager\RouteInterface;
use EchoFusion\RouteManager\RouteMatch\RouteMatch;
use PHPUnit\Framework\TestCase;

class RouteMatchTest extends TestCase
{
    public function testSetAndGetRoute()
    {
        $routeMock = $this->createMock(RouteInterface::class);
        $routeMatch = new RouteMatch();

        $routeMatch->setRoute($routeMock);

        $this->assertSame($routeMock, $routeMatch->getRoute());
    }

    public function testSetAndGetParams()
    {
        $routeMatch = new RouteMatch();

        $routeMatch->setParams(['param1' => 'value1', 'param2' => 'value2']);

        $this->assertSame(['param1' => 'value1', 'param2' => 'value2'], $routeMatch->getParams());
    }

    public function testSetParam()
    {
        $routeMatch = new RouteMatch();

        $routeMatch->setParam('param1', 'value1');
        $this->assertSame('value1', $routeMatch->getParam('param1'));

        $routeMatch->setParam('param2', '123');
        $this->assertSame(123, $routeMatch->getParam('param2')); // Should be cast to int
    }

    public function testGetParamReturnsNullForNonExistentParam()
    {
        $routeMatch = new RouteMatch();

        $this->assertNull($routeMatch->getParam('non_existent_param'));
    }
}
