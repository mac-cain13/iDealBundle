<?php

namespace Wrep\IDealBundle\IDeal;

use Wrep\IDealBundle\Exception\InvalidArgumentException;

class BIC
{
	private $code;

	public function __construct($code)
	{
		$this->setCode($code);
	}

	public function getCode()
	{
		return $this->code;
	}

	protected function setCode($code)
	{
		if ( !is_string($code) ) {
			throw new InvalidArgumentException('The given BIC isn\'t a string.');
		}

		$code = strtoupper($code);
		if (preg_match('/^([A-Z]){6}([0-9A-Z]){2}([0-9A-Z]{3})?$/', $code) == 0) {
			throw new InvalidArgumentException('The given BIC isn\'t valid. (' . $code . ')');
		}

		$this->code = $code;
	}

	public function __toString()
	{
		return $this->getCode();
	}
}
