<?php

namespace Models;

use Nette\Caching\Cache,
		Nette\Object,
		Nette\DI\Container;

/**
 * @property-read $models
 * @property-read Nette\Caching\Cache $cache
 */
abstract class BaseModel extends Object
{
	/** @var Container */
	protected $container;

	/** @var Nette\Http\SessionSection */
	protected $session;

	const EXPIRE = Cache::EXPIRATION;

	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	/**
	 * @param string $namespace
	 * @return Nette\Caching\Cache
	 */
	public function cache($namespace)
	{
		return $this->container->cacheLoader->getLoader($namespace);
	}

	protected function setSession($namespace = NULL, $expiretion='+14 days')
	{
		if ($namespace === NULL) {
			$namespace = get_class($this);
		}
		$this->session = $this->container->session->getSection($namespace);
		$this->session->setExpiration($expiretion);
	}

	public function getUser()
	{
		return $this->container->user;
	}

	public function getModels()
	{
		return $this->container->models;
	}

	public function getCache($namespace = NULL)
	{
		if (!$namespace) {
			$namespace = get_class($this);
		}
		return $this->cache($namespace);
	}

	public function __toString()
	{
		return $this->getReflection()->getName();
	}

	/**  @return \Nette\Security\Identity */
	public function getIdentity()
	{
		return $this->container->getByType('Nette\Security\User')->getIdentity();
	}

}
