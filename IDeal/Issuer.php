<?php

namespace Wrep\IDealBundle\IDeal;

class Issuer
{
	private $bic;
	private $name;

	public function __construct(BIC $bic, $name)
	{
		$this->bic = $bic;
		$this->name = $name;
	}

	public function getBIC()
	{
		return $this->bic;
	}

	public function getName()
	{
		return $this->name;
	}
}
