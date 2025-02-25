<?php

declare(strict_types=1);

namespace LenderSpender\LaravelWizard;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;

class WizardStepWithChildren extends WizardStep
{
    public WizardStep $parentStep;

    /** @var Collection<int, WizardStep> */
    public Collection $childSteps;

    /**
     * @param array<WizardStep> $children
     */
    public function __construct(WizardStep $parentStep, array $children)
    {
        $this->parentStep = $parentStep;
        $this->childSteps = collect($children);
    }

    public function getStepDetails(): StepDetails
    {
        return $this->parentStep->getStepDetails();
    }

    public function view(?Authenticatable $user): mixed
    {
        return $this->callFirstStepMethod('view', $user);
    }

    public function store(?Authenticatable $user): mixed
    {
        return $this->callFirstStepMethod('store', $user);
    }

    public function isCompleted(?Authenticatable $user): bool
    {
        $firstIncompleteChildStep = $this->childSteps
            ->first(fn (WizardStep $wizardStep) => ! $wizardStep->isCompleted($user));

        return $this->parentStep->isCompleted($user) && is_null($firstIncompleteChildStep);
    }

    public function isRequired(?Authenticatable $user): bool
    {
        $firstRequiredStep = $this->childSteps
            ->first(fn (WizardStep $wizardStep) => $wizardStep->isRequired($user));

        return $this->parentStep->isRequired($user) || ! is_null($firstRequiredStep);
    }

    public function getFirstChildStep(?Authenticatable $user): ?WizardStep
    {
        return $this->childSteps
            ->filter(fn (WizardStep $step) => $step->isRequired($user))
            ->first(fn (WizardStep $step) => ! $step->isCompleted($user));
    }

    public function callFirstStepMethod(string $methodName, ?Authenticatable $user): mixed
    {
        if ($this->parentStep->isCompleted($user) && $childStep = $this->getFirstChildStep($user)) {
            return $childStep->callMethod($methodName);
        }

        return $this->parentStep->callMethod($methodName);
    }
}
