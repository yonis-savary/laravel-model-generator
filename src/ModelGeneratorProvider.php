<?php

namespace YonisSavary\LaravelModelGenerator;

use Illuminate\Support\ServiceProvider;
use YonisSavary\LaravelModelGenerator\Console\Commands\CreateModels;

class ModelGeneratorProvider extends ServiceProvider
{
    public function boot()
    {
        $this->commands([CreateModels::class]);
    }
}