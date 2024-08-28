<?php

declare(strict_types=1);

namespace LenderSpender\LaravelWizard\Tests\Unit;

use Illuminate\Contracts\Auth\Authenticatable;
use LenderSpender\LaravelWizard\StepDetails;
use LenderSpender\LaravelWizard\Tests\TestCase;
use LenderSpender\LaravelWizard\WizardStep;

class WizardStepTest extends TestCase
{
    public function test_string_casting_is_set_to_step_slug(): void
    {
        $step = new class extends WizardStep {
            public function getStepDetails(): StepDetails
            {
                return new StepDetails('foo bar');
            }

            public function isCompleted(?Authenticatable $user): bool
            {
                return true;
            }

            public function isRequired(?Authenticatable $user): bool
            {
                return true;
            }
        };

        self::assertSame('foo-bar', (string) $step);
    }
}
