<?php

namespace Models;

use Nette\Caching\Cache,
		Nette\Object,
		Nette\DI\Container;

/**
 * @property-read $models
 * @property-read Nette\Caching\Cache $cache
 * @property-read Nette\Http\SessionSection
 */
abstract class BaseModel extends Object
{
	/** @var Container */
	protected $container;

	const EXPIRE = Cache::EXPIRATION;

	public function __construct(Container $container)
	{
		$this->container = $container;
	}

	public function getSession($expiretion='+14 days', $namespace = NULL)
	{
		if ($namespace === NULL) {
			$namespace = get_class($this);
		}
		$session = $this->container->session->getSection($namespace);
		$session->setExpiration($expiretion);
		return $session;
	}

	/** @return \Nette\Security\User */
	public function getUser()
	{
		return $this->container->user;
	}

	/** @return BaseModel */
	public function getModels()
	{
		return $this->container->models;
	}

	public function getCache($namespace = NULL)
	{
		if (!$namespace) {
			$namespace = get_class($this);
		}
		return $this->container->cacheLoader->getLoader($namespace);
	}

	public function __toString()
	{
		return $this->getReflection()->getName();
	}

	/**  @return \Nette\Security\Identity */
	public function getIdentity()
	{
		return $this->getUser()->getIdentity();
	}

}
