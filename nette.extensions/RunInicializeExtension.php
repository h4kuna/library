<?php

namespace h4kuna\Extensions;

use Nette,
	Nette\DI as NDI;

class RunInicializeExtension extends NDI\CompilerExtension
{
	/** @var array */
	private $defaults = [
		'services' => [],
	];

	public function loadConfiguration()
	{
		$this->config += $this->defaults;
		$builder = $this->getContainerBuilder();
		$config = NDI\Helpers::expand($this->config, $builder->parameters);
		$this->defaults = [];
		foreach ($config['services'] as $class) {
			$this->defaults[] = $name = $this->prefix(str_replace('\\', '_', is_object($class) ? $class->getEntity() : $class));

			$builder->addDefinition($name)
				->setAutowired(FALSE)
				->setFactory($class);
		}
	}

	public function afterCompile(Nette\PhpGenerator\ClassType $class)
	{
		$initialize = $class->getMethod('initialize');

		foreach ($this->defaults as $name) {
			$initialize->addBody('$this->{self::getMethodName(?)}();', [$name]);
		}
	}

}