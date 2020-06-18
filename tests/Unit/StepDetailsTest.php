<?php

declare(strict_types=1);

namespace LenderSpender\LaravelWizard\Tests\Unit;

use LenderSpender\LaravelWizard\StepDetails;
use LenderSpender\LaravelWizard\Tests\TestCase;

class StepDetailsTest extends TestCase
{
    public function test_get_name(): void
    {
        $stepDetails = new StepDetails('foo');

        self::assertSame('foo', $stepDetails->name());
    }

    public function test_get_title(): void
    {
        $stepDetails = new StepDetails('foo bar', 'bar-foo');

        self::assertSame('bar-foo', $stepDetails->title());
    }

    public function test_get_title_falls_back_to_name(): void
    {
        $stepDetails = new StepDetails('foo bar');

        self::assertSame('foo bar', $stepDetails->title());
    }

    public function test_get_slug(): void
    {
        $stepDetails = new StepDetails('foo bar', 'bar-foo', 'barbaz');

        self::assertSame('barbaz', $stepDetails->slug());
    }

    public function test_get_slug_is_generated_from_name(): void
    {
        $stepDetails = new StepDetails('foo bar');

        self::assertSame('foo-bar', $stepDetails->slug());
    }
}
