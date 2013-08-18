<?php

namespace Wrep\IDealBundle\IDeal;

use Wrep\IDealBundle\Exception\InvalidArgumentException;

class Acquirer
{
	private $url;
	private $certificatePath;

	public function __construct($url, $certificatePath)
	{
		$this->setUrl($url);
		$this->setCertificate($certificatePath);
	}

	public function getUrl()
	{
		return $this->url;
	}

	protected function setUrl($url)
	{
		if ( !filter_var($url, FILTER_VALIDATE_URL) ) {
			throw new InvalidArgumentException('The acquirer URL isn\'t a valid URL. (' . $url . ')');
		}

		$this->url = $url;
	}

	public function getCertificate()
	{
		return $this->certificatePath;
	}

	protected function setCertificate($path)
	{
		// Check if the merchant certificate exists
		if ( !is_file($path) ) {
			throw new InvalidArgumentException('The acquirer certificate doesn\'t exists. (' . $path . ')');
		}

		$this->certificatePath = $path;
	}
}
