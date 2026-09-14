<?php

use App\Http\Middleware\EnsureAdminActive;
use App\Http\Middleware\ResolvePublicRegion;
use App\Http\Middleware\SetAdminActiveRegion;
use App\Models\Region;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->alias([
            'admin.active' => EnsureAdminActive::class,
            'admin.region' => SetAdminActiveRegion::class,
            'public.region' => ResolvePublicRegion::class,
        ]);

        $middleware->redirectGuestsTo(fn () => route('admin.login'));

        // Render (like most PaaS) terminates TLS at its edge and forwards
        // plain HTTP to the container, with the original scheme passed via
        // X-Forwarded-Proto. Without trusting that header, Laravel thinks
        // every request is insecure and generates http:// asset/URL links
        // even on an https:// page — a mixed-content browser block. The
        // edge IP isn't fixed, so '*' (trust whichever proxy routes here)
        // is the standard pattern for this kind of platform.
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );

        // A disabled or non-existent {region} segment (e.g. /in/contact-us)
        // fails Region::resolveRouteBinding() before ResolvePublicRegion
        // middleware ever runs. Rather than 404ing, redirect to the default
        // region while preserving the rest of the requested path.
        // Laravel's Handler::prepareException() converts ModelNotFoundException
        // into NotFoundHttpException before any render() callback sees it, so
        // this must match on the wrapped exception and inspect getPrevious().
        $exceptions->render(function (NotFoundHttpException $e, Request $request) {
            $previous = $e->getPrevious();
            if (! $previous instanceof ModelNotFoundException || $previous->getModel() !== Region::class) {
                return null;
            }

            $default = Region::where('is_default', true)->where('is_active', true)->first();
            if (! $default) {
                return null;
            }

            $requestedCode = $request->segment(1) ?? '';
            $restOfPath = trim(substr($request->path(), strlen($requestedCode)), '/');
            $target = '/'.$default->code.($restOfPath !== '' ? '/'.$restOfPath : '');

            return redirect($target);
        });
    })->create();
