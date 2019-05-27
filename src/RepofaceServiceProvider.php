<?php

namespace Omnuvito\Repoface;

use Illuminate\Support\ServiceProvider;

class RepofaceServiceProvider extends ServiceProvider
{
    /**
     * The console commands.
     *
     * @var bool
     */
    protected $commands = [
        'Omnuvito\Repoface\Console\Commands\InterfaceMakeCommand',
        'Omnuvito\Repoface\Console\Commands\RepositoryMakeCommand',
    ];

    public function boot()
    {
        //
    }

    public function register()
    {
        $this->commands($this->commands);
    }
}
