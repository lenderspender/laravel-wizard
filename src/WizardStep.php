<?php

declare(strict_types=1);

namespace LenderSpender\LaravelWizard;

use Illuminate\Contracts\Auth\Authenticatable;

abstract class WizardStep
{
    abstract public function getStepDetails(): StepDetails;

    abstract public function isCompleted(?Authenticatable $user): bool;

    abstract public function isRequired(?Authenticatable $user): bool;

    public function callMethod(string $method): mixed
    {
        return app()->call([$this, $method], []); // @phpstan-ignore-line
    }

    public function equals(WizardStep $wizardStep): bool
    {
        return $this->getStepDetails()->slug() === $wizardStep->getStepDetails()->slug();
    }

    public function __toString(): string
    {
        return $this->getStepDetails()->slug();
    }
}
