<?php

declare(strict_types=1);

namespace EchoFusion\RouteManager\Exceptions;

use Exception;
use function sprintf;

class DuplicateRouteException extends Exception
{
    /**
     * @var string
     */
    protected $message = 'Another route with same name is exist!';

    public static function forName(string $name): self
    {
        return new self(sprintf('A route named "%s" is already registered.', $name));
    }
}
