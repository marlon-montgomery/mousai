<?php

namespace Common\Generators\Policy;

use Illuminate\Foundation\Console\PolicyMakeCommand;

class GeneratePolicy extends PolicyMakeCommand
{
    public function handle()
    {
        if (parent::handle() !== false) {
            // auto-register policy
            $path = app_path('Providers/AuthServiceProvider.php');
            $model = str_replace('/', '\\', $this->option('model'));
            $policy = $this->getNameInput();

            // add policy to providers
            $marker = "'App\\Model' => 'App\\Policies\\ModelPolicy',";
            file_put_contents($path, str_replace(
                $marker,
                "$marker\n        {$model}::class => $policy::class,",
                file_get_contents($path)
            ));

            // import policy and model
            $marker = 'use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;';
            $namespaceModel = $this->laravel->getNamespace().$model;
            $namespacePolicy = $this->qualifyClass($policy);

            file_put_contents($path, str_replace(
                $marker,
                "use {$namespaceModel};\nuse {$namespacePolicy};\n$marker",
                file_get_contents($path)
            ));
        }
    }

    protected function getStub()
    {
        if ($this->option('model')) {
            return __DIR__.'/stubs/policy.model.stub';
        }

        return parent::getStub();
    }
}
