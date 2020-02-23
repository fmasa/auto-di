<?php

declare(strict_types=1);

namespace Fmasa\AutoDI\Exceptions;

use Exception;

final class NoServiceRegistered extends Exception
{
    /**
     * @internal This constructor is not part of public API and may change between versions
     */
    public static function byPattern(string $pattern) : self
    {
        return new self(
            "No services were matched by registered using $pattern \n"
            . 'services with name matching the pattern were either not found or already registered by another extension'
        );
    }
}
