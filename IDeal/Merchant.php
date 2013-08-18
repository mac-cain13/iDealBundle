<?php

namespace Wrep\IDealBundle\IDeal;

use Wrep\IDealBundle\Exception\InvalidArgumentException;

class Merchant
{
	private $id;
	private $subId;
	private $certificatePath;
	private $certificatePassphrase;

	public function __construct($id, $subId, $certificatePath, $certificatePassphrase = null)
	{
		$this->setId($id);
		$this->setSubId($subId);
		$this->setCertificate($certificatePath, $certificatePassphrase);
	}

	public function getId()
	{
		return $this->id;
	}

	protected function setId($id)
	{
		// Validate the merchant ID, must be a 9 digit or less positive integer
		$id = (int)$id;
		if (!is_int($id) || $id <= 0) {
			throw new InvalidArgumentException('The merchant ID must a positive integer. (' . $id . ')');
		} else if (strlen($id) > 9) {
			throw new InvalidArgumentException('The merchant ID must be 9 digits or less. (' . $id . ')');
		}

		$this->id = $id;
	}

	public function getSubId()
	{
		return $this->subId;
	}

	protected function setSubId($subId)
	{
		// Validate the merchant sub-identifier
		$subId = (int)$subId;
		if (!is_int($subId) || $subId < 0) {
			throw new InvalidArgumentException('The merchant subID must a positive integer. (' . $subId . ')');
		} else if (strlen($subId) > 6) {
			throw new InvalidArgumentException('The merchant subID must be 6 digits or less. (' . $subId . ')');
		}

		$this->subId = $subId;
	}

	public function getCertificate()
	{
		return $this->certificatePath;
	}

	public function getCertificatePassphrase()
	{
		return $this->certificatePassphrase;
	}

	protected function setCertificate($path, $passphrase = null)
	{
		// Check if the merchant certificate exists
		if ( !is_file($path) ) {
			throw new InvalidArgumentException('The merchant certificate doesn\'t exists. (' . $path . ')');
		}

		$this->certificatePath = $path;
		$this->certificatePassphrase = $passphrase;
	}
}
