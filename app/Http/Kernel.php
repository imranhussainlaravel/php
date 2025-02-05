<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;
use Illuminate\Support\Facades\Log;

class Kernel extends HttpKernel
{
    /**
     * The application's global HTTP middleware stack.
     *
     * These middleware are run during every request to your application.
     *
     * @var array
     */
    protected $middleware = [
        // Middleware that applies globally
    ];

    /**
     * The application's route middleware groups.
     *
     * @var array
     */
    protected $middlewareGroups = [
        'web' => [
           
        ],

        'api' => [
            // \App\Http\Middleware\CheckOrigin::class, // Add this line
            \Illuminate\Routing\Middleware\ThrottleRequests::class . ':api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    /**
     * The application's route middleware.
     *
     * These middleware may be assigned to groups or used individually.
     *
     * @var array
     */
    // protected $routeMiddleware = [
    //     // Other middlewares
    //     'adminre' => \App\Http\Middleware\RedirectIfAdminAuthenticated::class,
    // ];
    protected $routeMiddleware = [
        'adminre' => \App\Http\Middleware\RedirectIfAdminAuthenticated::class,
        'check.origin' => \App\Http\Middleware\CheckOrigin::class, // Uncomment this line
    ];
    // protected $routeMiddleware = [
    //     // Other middlewares
    //     'adminre' => \App\Http\Middleware\RedirectIfAdminAuthenticated::class,
    //     'check.origin' => \App\Http\Middleware\CheckOrigin::class, // Add this line
    // ];
}
