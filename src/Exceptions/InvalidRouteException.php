<?php

declare(strict_types=1);

namespace EchoFusion\RouteManager\Exceptions;

use InvalidArgumentException;

/**
 * A route definition is not valid (bad path pattern, missing keys in a configuration array).
 */
class InvalidRouteException extends InvalidArgumentException
{
}
