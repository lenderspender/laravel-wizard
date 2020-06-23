# Laravel wizard
Laravel wizard is an easy way to create a clear way of conditional steps. 

## Installation

You can install the package via composer:
```bash
composer require lenderspender/laravel-wizard
```

## Usage

### Creating steps
A step is where the logic and view for that particular step is defined. Steps can also be conditional.

You are able to inject any dependencies you need into the step's `view` and `store` methods. The Laravel service container will automatically inject all dependencies that are type-hinted.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Request;
use LenderSpender\LaravelWizard\StepDetails;
use LenderSpender\LaravelWizard\WizardStep;
use Symfony\Component\HttpFoundation\Response;

class FirstStep extends WizardStep
{
    public function view(): Response
    {
        return view('steps.first-step');
    }

    public function store(Request $request): void
    {
        $data = $request->validate([
            'foo' => 'required',
        ]);

        SomeModel::update(['foo' => $data['foo']]);
    }

    public function getStepDetails(): StepDetails
    {
        return new StepDetails('First step', 'This is the first step', 'first-step');
    }

    public function isCompleted(?Authenticatable $user): bool
    {
        return true;
    }

    public function isRequired(?Authenticatable $user): bool
    {
        return true;
    }
}
``` 

##### Throwing additional errors
Sometimes you wish to throw additional errors. When the `StoreStepException` is thrown from the `store` method in your step.
Users are automatically redirected back to the previous page with errors.

```php
use LenderSpender\LaravelWizard\Exceptions\StoreStepException;

public function store(Authenticatable $user)
{
    if (! $user->emailVerified()) {
        throw new StoreStepException('Email address is not yet verified');
    }

    $user->update(['foo' => 'bar']);
}
``` 

### Setup controller and routes

Create a new Controller that will handle your steps.

```php
<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use Illuminate\Contracts\Auth\Authenticatable;
use LenderSpender\LaravelWizard\Wizard;
use Symfony\Component\HttpFoundation\Response;

class WizardController
{
    public function show(string $step, Authenticatable $user): Response
    {
        return $this->getWizard($user)
            ->view($step);
    }

    public function store(string $step, Authenticatable $user): Response
    {
        $wizard = $this->getWizard($user);

        if ($redirect = $wizard->store($step)) {
            return $redirect;
        }

        return redirect(action(
            [WizardController::class, 'show'],
            [
                'step' => (string) $wizard->nextStep($wizard->getStepFromSlug($step)),
            ]
        ));
    }

    private function getWizard(Authenticatable $user): Wizard
    {
        return new Wizard(
            [
                new FirstStep(),
                new SecondStep(),
            ],
            false,
            $user
        );
    }
}
``` 

Then you should define your routes:

```php

Route::get('/wizard/{step}', [WizardController::class, 'show']);
Route::post('/wizard/{step}', [WizardController::class, 'store']);
```

