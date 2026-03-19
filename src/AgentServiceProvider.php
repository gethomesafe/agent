<?php

namespace Jenssegers\Agent;

use Illuminate\Support\ServiceProvider;

class AgentServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    /**
     * Register the service provider.
     */
    public function register()
    {
        $this->app->singleton('agent', function ($app) {
            $agent = new Agent();
            $agent->setHttpHeaders($app['request']->server());
            $agent->setUserAgent($app['request']->userAgent());
            return $agent;
        });

        $this->app->alias('agent', Agent::class);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return ['agent', Agent::class];
    }
}
