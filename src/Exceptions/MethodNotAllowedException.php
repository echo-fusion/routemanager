<?php

declare(strict_types=1);

namespace EchoFusion\RouteManager\Exceptions;

use function implode;
use function sprintf;
use function strtoupper;

/**
 * The path exists, but not for the request's method (an HTTP 405).
 *
 * It extends {@see RouteNotFoundException}, so code that only handles "not found" keeps working.
 */
class MethodNotAllowedException extends RouteNotFoundException
{
    /**
     * @var list<string>
     */
    private array $allowedMethods = [];

    /**
     * @param list<string> $allowedMethods
     */
    public static function forMethod(string $method, string $path, array $allowedMethods): self
    {
        $exception = new self(sprintf(
            'Method %s is not allowed for %s; allowed: %s.',
            strtoupper($method),
            $path,
            implode(', ', $allowedMethods),
        ));
        $exception->allowedMethods = $allowedMethods;

        return $exception;
    }

    /**
     * The methods to put in the `Allow` header of the 405 response.
     *
     * @return list<string>
     */
    public function getAllowedMethods(): array
    {
        return $this->allowedMethods;
    }
}
