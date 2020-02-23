<?php

declare(strict_types=1);

namespace Fmasa\AutoDI\Exceptions;

use Exception;

final class IncompleteServiceDefinition extends Exception
{
    public static function fromDefinition(array $definition) : self
    {
        return new self(
            "Service definition is missing either 'implement' or 'class' key:\n\n"
                . "definition: \n"
                . var_export($definition, true)
        );
    }
}
