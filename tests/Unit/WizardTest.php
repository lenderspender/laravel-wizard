<?php

declare(strict_types=1);

namespace LenderSpender\LaravelWizard\Tests\Unit;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Redirector;
use Illuminate\View\View;
use LenderSpender\LaravelWizard\Exceptions\InvalidStepOrderException;
use LenderSpender\LaravelWizard\Exceptions\StepNotFoundException;
use LenderSpender\LaravelWizard\Exceptions\StoreStepException;
use LenderSpender\LaravelWizard\StepDetails;
use LenderSpender\LaravelWizard\Tests\TestCase;
use LenderSpender\LaravelWizard\Tests\Unit\Stubs\TestStep;
use LenderSpender\LaravelWizard\Wizard;
use LenderSpender\LaravelWizard\WizardStep;
use Mockery;

class WizardTest extends TestCase
{
    public function test_wizard_is_added_to_view_from_step(): void
    {
        $view = Mockery::spy(View::class);
        $wizard = new Wizard([
            $step = new TestStep(new StepDetails('First step'), true, true, $view),
        ]);

        $view->expects('with')
            ->with([
                'wizard' => $wizard,
                'currentStep' => $step,
            ])
            ->andReturnSelf();

        $wizard->view('first-step');
    }

    public function test_step_not_found_exception_is_thrown(): void
    {
        $wizard = new Wizard([]);

        try {
            $wizard->view('foo-step');
        } catch (StepNotFoundException $e) {
            self::assertSame('No results for step [foo-step]', $e->getMessage());

            return;
        }

        $this->fail();
    }

    public function test_invalid_step_order_is_thrown_when_step_order_is_preserved_and_step_is_not_allowed(): void
    {
        $wizard = new Wizard([
            new TestStep(new StepDetails('First step'), false, true, Mockery::spy(View::class)),
            new TestStep(new StepDetails('Second step'), false, true, Mockery::spy(View::class)),
        ], true);

        try {
            $wizard->view('second-step');
        } catch (InvalidStepOrderException $e) {
            self::assertEquals('Step [TestStep] is not allowed.', $e->getMessage());

            return;
        }

        $this->fail();
    }

    public function test_step_is_always_allowed_when_order_is_not_preserved(): void
    {
        $wizard = new Wizard([
            new TestStep(new StepDetails('First step')),
            $secondStep = new TestStep(new StepDetails('Second step')),
        ], false);

        self::assertTrue($wizard->stepIsAllowed($secondStep));
    }

    public function test_can_store_step(): void
    {
        $wizard = new Wizard([
            new TestStep(new StepDetails('First step')),
        ]);

        $response = $wizard->store('first-step');

        self::assertInstanceOf(RedirectResponse::class, $response);
    }

    public function test_storing_fails_redirects_back_with_errors(): void
    {
        $step = new class() extends WizardStep {
            public function getStepDetails(): StepDetails
            {
                return new StepDetails('first step');
            }

            public function isCompleted(?Authenticatable $user): bool
            {
                return false;
            }

            public function isRequired(?Authenticatable $user): bool
            {
                return true;
            }

            public function store(): void
            {
                throw new StoreStepException('foo bar is not allowed');
            }
        };
        $wizard = new Wizard([
            $step,
        ]);
        $redirector = $this->spy(Redirector::class);
        $redirector->shouldReceive('back->withErrors')->with('foo bar is not allowed')->andReturn('foo');

        $response = $wizard->store('first-step');

        self::assertEquals('foo', $response);
    }

    public function test_next_step_is_correct(): void
    {
        $user = Mockery::mock(Authenticatable::class);

        $wizard = new Wizard([
            $firstStep = new TestStep(new StepDetails('First step'), true, true),
            $secondStep = Mockery::mock(WizardStep::class),
            $thirdStep = new TestStep(new StepDetails('Third step'), false, true),
        ], false, $user);

        $secondStep->expects('isCompleted')->with($user)->andReturn(true);
        $secondStep->expects('isRequired')->with($user)->andReturn(true);

        self::assertEquals($thirdStep, $wizard->nextStep($firstStep));
    }

    public function test_first_step_is_correct(): void
    {
        $user = Mockery::mock(Authenticatable::class);

        $wizard = new Wizard([
            $firstStep = new TestStep(new StepDetails('First step'), true, false),
            $secondStep = new TestStep(new StepDetails('First step'), true, true),
            $thirdStep = new TestStep(new StepDetails('Third step'), false, true),
        ], false, $user);

        self::assertTrue($thirdStep->equals($wizard->firstStep()));
    }

    public function test_getting_steps(): void
    {
        $wizard = new Wizard([
            new TestStep(new StepDetails('First step')),
            new TestStep(new StepDetails('Second step')),
        ]);

        $steps = $wizard->getSteps()
            ->map(fn (WizardStep $step) => $step->getStepDetails()->name())
            ->toArray();

        self::assertEquals(['First step', 'Second step'], $steps);
    }

    public function test_step_is_required(): void
    {
        $wizard = new Wizard([
            $firstStep = new TestStep(new StepDetails('First step'), true, false),
            $secondStep = new TestStep(new StepDetails('Second step'), true, true),
        ]);

        self::assertFalse($wizard->stepIsRequired($firstStep));
        self::assertTrue($wizard->stepIsRequired($secondStep));
    }

    public function test_step_can_be_initialised_in_multiple_ways(): void
    {
        $wizard = new Wizard([
            $firstStep = new TestStep(new StepDetails('First step'), true, false),
            TestStep::class,
            [TestStep::class => ['stepDetails' => new StepDetails('Third step')]],
        ]);

        $steps = $wizard->getSteps()->map(fn (WizardStep $step) => $step->getStepDetails()->name())->toArray();

        self::assertEquals(['First step', 'foo', 'Third step'], $steps);
    }
}
