<?php

declare(strict_types=1);

namespace LenderSpender\LaravelWizard\Exceptions;

use RuntimeException;

class StoreStepException extends RuntimeException
{
    public function __construct(string $errorMessage)
    {
        parent::__construct($errorMessage);
    }
}
