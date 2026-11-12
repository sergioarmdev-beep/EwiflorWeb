<?php

use App\Http\Middleware\SetDatabaseUser;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Exceptions\BusinessRuleException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->web(append: [
            SetDatabaseUser::class,
        ]);
        $middleware->web(append: [
            \App\Http\Middleware\SessionTimeout::class,
        ]);
        $middleware->alias([
            'role' => \App\Http\Middleware\CheckRole::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        $exceptions->render(function (BusinessRuleException $e, $request) {
            if ($request->expectsJson()) {
                return response()->json([
                    'error'   => $e->getMessage(),
                    'type'    => 'business_rule',
                ], 422);
            }

            return back()
                ->withInput()
                ->with('error', $e->getMessage());
        });
    })

    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
