<?php declare(strict_types=1);

namespace App\Exceptions;

use Throwable;

abstract class RuntimeException extends \RuntimeException
{

	protected function __construct(string $message = '', ?Throwable $previous = null)
	{
		parent::__construct($message, $previous === null ? 0 : $previous->getCode(), $previous);
	}

	public function toLogic(): LogicException
	{
		return new LogicException($this->getMessage(), $this->getCode(), $this->getPrevious());
	}

}
