<?php

declare(strict_types=1);

namespace EchoFusion\RouteManager\Exceptions;

use Exception;

class DuplicateRouteException extends Exception
{
    /**
     * @var string
     */
    protected $message = 'Another route with same name is exist!';
}
