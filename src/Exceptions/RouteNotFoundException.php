<?php

declare(strict_types=1);

namespace EchoFusion\RouteManager\Exceptions;

use Exception;

class RouteNotFoundException extends Exception
{
    /**
     * @var string
     */
    protected $message = '404 Not Found!';
}
