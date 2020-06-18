<?php

declare(strict_types=1);

namespace LenderSpender\LaravelWizard;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use LenderSpender\LaravelWizard\Exceptions\InvalidStepOrderException;
use LenderSpender\LaravelWizard\Exceptions\StepNotFoundException;
use LenderSpender\LaravelWizard\Exceptions\StoreStepException;

class Wizard
{
    /** @var \Illuminate\Support\Collection<\LenderSpender\LaravelWizard\WizardStep> */
    private Collection $steps;
    private ?WizardStep $currentStep = null;
    private bool $preserveStepOrder;
    private ?Authenticatable $user;

    /**
     * @param array<string|\LenderSpender\LaravelWizard\WizardStep> $steps
     */
    public function __construct(array $steps, bool $preserveStepOrder = false, Authenticatable $user = null)
    {
        $this->steps = collect($steps);
        $this->preserveStepOrder = $preserveStepOrder;
        $this->user = $user;
    }

    public function view(string $step): View
    {
        $step = $this->getStepFromSlug($step);

        if ($this->preserveStepOrder && ! $this->stepIsAllowed($step)) {
            throw new InvalidStepOrderException($step);
        }

        /** @var View $view */
        $view = $step->callMethod('view');

        return $view->with([
            'wizard' => $this,
        ]);
    }

    /**
     * @return \Illuminate\Http\Response|void|null
     */
    public function store(string $step)
    {
        try {
            $response = $this->getStepFromSlug($step)
                ->callMethod('store');
        } catch (StoreStepException $exception) {
            $response = app(Redirector::class)
                ->back()
                ->withErrors($exception->getMessage());
        }

        return $response;
    }

    public function nextStep(WizardStep $currentStep): ?WizardStep
    {
        return $this->steps
            ->filter(fn (WizardStep $step) => $step->isRequired($this->user))
            ->filter(fn (WizardStep $step) => ! $step->isCompleted($this->user))
            ->first(fn (WizardStep $step) => ! $step->equals($currentStep));
    }

    public function firstStep(): WizardStep
    {
        return $this->steps
            ->filter(fn (WizardStep $step) => $step->isRequired($this->user))
            ->first(fn (WizardStep $step) => ! $step->isCompleted($this->user));
    }

    public function getSteps(): Collection
    {
        return $this->steps;
    }

    public function stepIsAllowed(WizardStep $wizardStep): bool
    {
        if (! $this->preserveStepOrder) {
            return true;
        }

        $user = auth()->user();

        return $this->steps
            ->takeWhile(function (WizardStep $step) use ($wizardStep, $user) {
                return ! $step->isRequired($user) || ($step->isRequired($user) && $step->isCompleted($user)) || $step->equals($wizardStep);
            })
            ->contains(fn (WizardStep $step) => $step->equals($wizardStep));
    }

    public function stepIsRequired(WizardStep $wizardStep): bool
    {
        return $this->steps
            ->first(fn (WizardStep $step) => $step->equals($wizardStep))
            ->isRequired(auth()->user());
    }

    protected function getStepFromSlug(string $stepSlug): WizardStep
    {
        $wizardStep = $this->steps->first(fn (WizardStep $step) => $step->getStepDetails()->slug() === $stepSlug);

        if (! $wizardStep) {
            throw new StepNotFoundException($stepSlug);
        }

        return $wizardStep;
    }
}
