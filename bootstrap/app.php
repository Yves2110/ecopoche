<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Headers de sécurité HTTP sur toutes les réponses
        $middleware->append(\App\Http\Middleware\SecurityHeaders::class);
        // Vérification compte actif sur toutes les requêtes authentifiées
        $middleware->append(\App\Http\Middleware\CheckActive::class);

        $middleware->alias([
            'role'   => \App\Http\Middleware\CheckRole::class,
            'active' => \App\Http\Middleware\CheckActive::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (\Symfony\Component\HttpKernel\Exception\HttpException $e, Request $request) {
            $code = $e->getStatusCode();
            if (in_array($code, [403, 404, 429, 500]) && !$request->expectsJson()) {
                return response()->view("errors.{$code}", ['exception' => $e], $code);
            }
        });
    })
    ->create();
