<?php

namespace Wrep\IDealBundle\IDeal;

class Issuer extends IssuerId
{
	private $name;
	private $country;

	public function __construct($id, $name, $country)
	{
		parent::__construct($id);
		$this->name = $name;
		$this->country = $country;
	}

	public function getName()
	{
		return $this->name;
	}

	public function getCountry()
	{
		return $this->country;
	}
}