<?php

namespace Wrep\IDealBundle\IDeal;

use Wrep\IDealBundle\Exception\InvalidArgumentException;

class Consumer
{
	private $name;
	private $bic;
	private $iban;

	public function __construct($name, $iban, BIC $bic = null)
	{
		$this->setName($name);
		$this->setBIC($bic);
		$this->setIban($iban);
	}

	public function getName()
	{
		return $this->name;
	}

	protected function setName($name)
	{
		$this->name = $name;
	}

	public function getIban()
	{
		return $this->iban;
	}

	protected function setIban($iban)
	{
		$this->iban = $iban;
	}

	public function getBIC()
	{
		return $this->bic;
	}

	protected function setBIC(BIC $bic = null)
	{
		$this->bic = $bic;
	}
}
