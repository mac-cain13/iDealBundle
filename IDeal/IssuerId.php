<?php

namespace Wrep\IDealBundle\IDeal;

class IssuerId
{
	private $id;

	public function __construct($id)
	{
		$this->id = $id;
	}

	public function getId()
	{
		return $this->id;
	}
}