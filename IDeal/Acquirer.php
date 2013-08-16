<?php

namespace Wrep\IDealBundle\IDeal;

class Acquirer
{
	private $url;
	private $certificate;

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
		$this->url = $url;
	}

	public function getCertificate()
	{
		return $this->certificate;
	}

	protected function setCertificate($path)
	{
		// Check if the merchant certificate exists
		if ( !is_file($path) ) {
			throw new \RuntimeException('The acquirer certificate doesn\'t exists. (' . $path . ')');
		}

		$this->certificate = $path;
	}
}