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
abstract class BaseModel extends Object {

    const ZERO_TIME = '00-00-00 00:00:00';

    /** @var Container */
    protected $context;

    const ITEM_PER_PAGE = 50;
    const EXPIRE = Cache::EXPIRATION;

    public function __construct(Container $context) {
        $this->context = $context;
    }

    public function getSession($expiretion = '+14 days', $namespace = NULL) {
        if ($namespace === NULL) {
            $namespace = get_class($this);
        }
        $session = $this->context->nette->createSection($namespace);
        $session->setExpiration($expiretion);
        return $session;
    }

    /** @return \Nette\Security\User */
    public function getUser() {
        return $this->context->user;
    }

    public function getCache($namespace = NULL) {
        if (!$namespace) {
            $namespace = get_class($this);
        }
        return $this->context->nette->createCache($namespace);
    }

    public function __toString() {
        return $this->getReflection()->getName();
    }

    /**  @return \Nette\Security\Identity */
    public function getIdentity() {
        return $this->getUser()->getIdentity();
    }

    public function getParameters($key = NULL) {
        $out = $this->context->parameters;

        if (!$key) {
            return $out;
        }

        foreach (explode('.', $key) as $v) {
            $out = $out[$v];
        }
        return $out;
    }

}
