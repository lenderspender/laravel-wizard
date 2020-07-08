<?php

declare(strict_types=1);

namespace LenderSpender\LaravelWizard\Tests\Unit\Stubs;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use LenderSpender\LaravelWizard\StepDetails;
use LenderSpender\LaravelWizard\WizardStep;

class TestStep extends WizardStep
{
    private View $view;
    private StepDetails $stepDetails;
    private bool $isCompleted;
    private bool $isRequired;

    public function __construct(StepDetails $stepDetails = null, bool $isCompleted = true, bool $isRequired = true, View $view = null)
    {
        $this->stepDetails = $stepDetails ?? new StepDetails('foo');
        $this->isCompleted = $isCompleted;
        $this->isRequired = $isRequired;

        if ($view) {
            $this->view = $view;
        }
    }

    public function getStepDetails(): StepDetails
    {
        return $this->stepDetails;
    }

    public function view(): View
    {
        return $this->view;
    }

    public function store(): RedirectResponse
    {
        return new RedirectResponse('/');
    }

    public function isCompleted(?Authenticatable $user): bool
    {
        return $this->isCompleted;
    }

    public function isRequired(?Authenticatable $user): bool
    {
        return $this->isRequired;
    }
}
