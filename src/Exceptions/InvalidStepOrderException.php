<?php

declare(strict_types=1);

namespace LenderSpender\LaravelWizard\Exceptions;

use LenderSpender\LaravelWizard\WizardStep;
use RuntimeException;

class InvalidStepOrderException extends RuntimeException
{
    public function __construct(WizardStep $currentStep)
    {
        $className = class_basename($currentStep);

        parent::__construct("Step [{$className}] is not allowed.");
    }
}
