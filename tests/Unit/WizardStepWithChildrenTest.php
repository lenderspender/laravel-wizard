<?php

declare(strict_types=1);

namespace LenderSpender\LaravelWizard\Tests\Unit;

use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use LenderSpender\LaravelWizard\StepDetails;
use LenderSpender\LaravelWizard\Tests\TestCase;
use LenderSpender\LaravelWizard\Tests\Unit\Stubs\TestStep;
use LenderSpender\LaravelWizard\WizardStepWithChildren;
use Mockery;

class WizardStepWithChildrenTest extends TestCase
{
    public function test_wizard_step_with_children_is_completed_once_parent_and_children_are_completed(): void
    {
        $incompleteParentStep = new WizardStepWithChildren(
            new TestStep(new StepDetails('First step'), false),
            [
                new TestStep(new StepDetails('Child step'), true),
            ]
        );
        $completeParentStepIncompleteChild = new WizardStepWithChildren(
            new TestStep(new StepDetails('First step'), true),
            [
                new TestStep(new StepDetails('Child step'), false),
            ]
        );
        $completedSteps = new WizardStepWithChildren(
            new TestStep(new StepDetails('First step'), true),
            [
                new TestStep(new StepDetails('Child step'), true),
            ]
        );

        self::assertFalse($incompleteParentStep->isCompleted(null));
        self::assertFalse($completeParentStepIncompleteChild->isCompleted(null));
        self::assertTrue($completedSteps->isCompleted(null));
    }

    public function test_wizard_step_with_children_is_required_once_parent_and_children_are_completed(): void
    {
        $parentStepRequiredOnly = new WizardStepWithChildren(
            new TestStep(new StepDetails('First step'), true, true),
            [
                new TestStep(new StepDetails('Child step'), true, false),
            ]
        );
        $childStepRequiredOnly = new WizardStepWithChildren(
            new TestStep(new StepDetails('First step'), true, false),
            [
                new TestStep(new StepDetails('Child step'), true, true),
            ]
        );
        $noRequiredStepsLeft = new WizardStepWithChildren(
            new TestStep(new StepDetails('First step'), true, true),
            [
                new TestStep(new StepDetails('Child step'), true, true),
            ]
        );
        $notRequired = new WizardStepWithChildren(
            new TestStep(new StepDetails('First step'), true, false),
            [
                new TestStep(new StepDetails('Child step'), true, false),
            ]
        );

        self::assertTrue($parentStepRequiredOnly->isRequired(null));
        self::assertTrue($childStepRequiredOnly->isRequired(null));
        self::assertTrue($noRequiredStepsLeft->isRequired(null));
        self::assertFalse($notRequired->isRequired(null));
    }

    public function test_wizard_step_view_method_is_first_called_on_parent(): void
    {
        $viewResponse = Mockery::mock(View::class);

        $wizardStep = new WizardStepWithChildren(
            new TestStep(new StepDetails('First step'), false, true, $viewResponse),
            []
        );

        self::assertSame($viewResponse, $wizardStep->view(null));
    }

    public function test_wizard_step_view_method_is_called_on_child_when_parent_is_completed(): void
    {
        $viewResponse = Mockery::mock(View::class);

        $wizardStep = new WizardStepWithChildren(
            new TestStep(new StepDetails('First step'), true),
            [
                new TestStep(new StepDetails('Child step'), false, true, $viewResponse),
            ]
        );

        self::assertSame($viewResponse, $wizardStep->view(null));
    }

    public function test_wizard_step_view_method_is_called_on_parent_when_all_steps_are_completed(): void
    {
        $viewResponse = Mockery::mock(View::class);

        $wizardStep = new WizardStepWithChildren(
            new TestStep(new StepDetails('First step'), true, true, $viewResponse),
            [
                new TestStep(new StepDetails('Child step'), true, true),
            ]
        );

        self::assertSame($viewResponse, $wizardStep->view(null));
    }

    public function test_wizard_step_store_method_is_first_called_on_parent(): void
    {
        $storeResponse = Mockery::mock(RedirectResponse::class);

        $wizardStep = new WizardStepWithChildren(
            new TestStep(new StepDetails('First step'), false, true, null, $storeResponse),
            []
        );

        self::assertSame($storeResponse, $wizardStep->store(null));
    }

    public function test_wizard_step_store_method_is_called_on_child_when_parent_is_completed(): void
    {
        $storeResponse = Mockery::mock(RedirectResponse::class);

        $wizardStep = new WizardStepWithChildren(
            new TestStep(new StepDetails('First step'), true),
            [
                new TestStep(new StepDetails('Child step'), false, true, null, $storeResponse),
            ]
        );

        self::assertSame($storeResponse, $wizardStep->store(null));
    }
}
