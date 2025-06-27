<?php declare(strict_types=1);

namespace h4kuna\Extensions;

use Nette\DI\CompilerExtension;
use Nette\PhpGenerator\ClassType;

final class RunInitializeExtension extends CompilerExtension
{

    /** @var list<string> */
    private array $services = [];

    public function loadConfiguration(): void
    {
        $builder = $this->getContainerBuilder();
        foreach ($this->config['services'] ?? [] as $class) {
            $this->services[] = $name = $this->prefix(str_replace('\\', '_', is_object($class) ? $class->getEntity() : $class));

            $builder->addDefinition($name)
                ->setAutowired(false)
                ->setFactory($class);
        }
    }

    public function afterCompile(ClassType $class): void
    {
        $initialize = $class->getMethod('initialize');

        foreach ($this->services as $name) {
            $initialize->addBody('$this->{self::getMethodName(?)}();', [$name]);
        }
    }

}
