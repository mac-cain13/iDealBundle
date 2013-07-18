<?php

namespace Wrep\IDealBundle\IDeal;

class Issuer
{
	private $id;
	private $name;
	private $country;

	public function __construct($id, $name, $country)
	{
		$this->id = $id;
		$this->name = $name;
		$this->country = $country;
	}

	public function getId()
	{
		return $this->id;
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