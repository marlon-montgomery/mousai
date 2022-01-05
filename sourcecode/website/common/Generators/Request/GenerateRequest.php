<?php

namespace Common\Generators\Request;

use Illuminate\Foundation\Console\RequestMakeCommand;
use Str;
use Symfony\Component\Console\Input\InputOption;

class GenerateRequest extends RequestMakeCommand
{
    protected function getStub()
    {
        return __DIR__.'/stubs/request.stub';
    }

    /**
     * @param  string  $name
     * @return string
     */
    protected function buildClass($name)
    {
        $stub = parent::buildClass($name);

        $model = Str::snake($this->option('model'));
        $table = Str::plural($model);

        $stub = str_replace('DummyModel', $model, $stub);
        $stub = str_replace('DummyTable', $table, $stub);

        return $stub;
    }

    /**
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['model', 'm', InputOption::VALUE_OPTIONAL, 'The model that the request applies to.'],
        ];
    }
}
