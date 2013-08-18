<?php

namespace Wrep\IDealBundle\IDeal;

use Wrep\IDealBundle\Exception\InvalidArgumentException;

class Issuer
{
	private $bic;
	private $name;

	public function __construct(BIC $bic, $name)
	{
		$this->setBIC($bic);
		$this->setName($name);
	}

	public function getBIC()
	{
		return $this->bic;
	}

	protected function setBIC(BIC $bic)
	{
		$this->bic = $bic;
	}

	public function getName()
	{
		return $this->name;
	}

	protected function setName($name)
	{
		if ( !is_string($name) || strlen($name) == 0 ) {
			throw new InvalidArgumentException('Name must be a non-empty string. (' . $name . ')');
		}

		$this->name = $name;
	}
}
