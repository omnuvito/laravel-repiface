<?php

namespace Omnuvito\Repoface\Console\Commands;

use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Symfony\Component\Console\Input\InputOption;

class RepositoryMakeCommand extends GeneratorCommand
{
    /**
     * STUBS RELATIVE PATH
     */
    const STUBS_RELATIVE_PATH = DIRECTORY_SEPARATOR.'Stubs'.DIRECTORY_SEPARATOR.'Repository'.DIRECTORY_SEPARATOR;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'make:repository';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new repository class';

    /**
     * The type of class being generated.
     *
     * @var string
     */
    protected $type = 'Repository';

    /**
     * Get the stub file for the generator.
     *
     * @return string
     */
    protected function getStub()
    {
        $stub = null;

        if ($this->option('interface')) {
            $stub = self::STUBS_RELATIVE_PATH . 'repository.interface.stub';
        }

        $stub = $stub ?? self::STUBS_RELATIVE_PATH .'repository.plain.stub';

        return __DIR__.$stub;
    }

    /**
     * Build the class with the given name.
     *
     * @param  string  $name
     * @return string
     * @throws FileNotFoundException
     */
    protected function buildClass($name)
    {
        $repositoryNamespace = $this->getNamespace($name);

        $replace = [];

        if ($this->option('interface')) {
            $replace = $this->buildInterfaceReplacements($replace);
        }

        $replace["use {$repositoryNamespace}\Controller;\n"] = '';

        return str_replace(
            array_keys($replace), array_values($replace), parent::buildClass($name)
        );
    }

    /**
     * Build the interface replacement values.
     *
     * @param  array $replace
     * @return array
     */
    protected function buildInterfaceReplacements(array $replace)
    {
        $interfaceClass = $this->parseInterface($this->option('interface'));

        if (! class_exists($interfaceClass)) {
            if ($this->confirm("A {$interfaceClass} interface does not exist. Do you want to generate it?", true)) {
                $this->call('make:interface', ['name' => $interfaceClass]);
            }
        }

        return array_merge($replace, [
            'DummyFullInterfaceFile' => $interfaceClass,
            'DummyInterfaceFile' => class_basename($interfaceClass),
            'DummyInterfaceVariable' => lcfirst(class_basename($interfaceClass)),
        ]);
    }

    /**
     * Get the fully-qualified interface class name.
     *
     * @param  string $interface
     * @return string
     *
     * @throws InvalidArgumentException
     */
    protected function parseInterface($interface)
    {
        if (preg_match('([^A-Za-z0-9_/\\\\])', $interface)) {
            throw new InvalidArgumentException('Interface name contains invalid characters.');
        }

        $interface = trim(str_replace('/', '\\', $interface), '\\');

        if (! Str::startsWith($interface, $rootNamespace = $this->getInterfaceNamespace())) {
            $interface = $rootNamespace.$interface;
        }

        return $interface;
    }

    /**
     * Get the default namespace for the class.
     *
     * @param  string $rootNamespace
     * @return string
     */
    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace.'\Repositories';
    }

    /**
     * Get namespace for given interface.
     *
     * @return string
     */
    protected function getInterfaceNamespace()
    {
        return $this->laravel->getNamespace().'Repositories\\Interfaces\\';
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['interface', 'i', InputOption::VALUE_OPTIONAL, 'Generate a repository for the given interface.'],
        ];
    }
}
