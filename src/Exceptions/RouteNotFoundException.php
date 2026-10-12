<?php

declare(strict_types=1);

namespace EchoFusion\RouteManager\Exceptions;

use Exception;
use function sprintf;

class RouteNotFoundException extends Exception
{
    /**
     * @var string
     */
    protected $message = '404 Not Found!';

    public static function forRequest(string $method, string $path): self
    {
        return new self(sprintf('No route matches %s %s.', mb_strtoupper($method), $path));
    }
}
