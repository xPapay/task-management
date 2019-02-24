<?php

namespace App\Http\Middleware;

use Closure;
use App\Demo\DemoEnv;

class CheckForDemoMode
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->isDemo()) {
            new DemoEnv();
        }
        return $next($request);
    }

    protected function isDemo()
    {
        return env('APP_ENV') === 'demo';
    }
}
