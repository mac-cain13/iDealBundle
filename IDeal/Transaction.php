<?php

namespace Wrep\IDealBundle\IDeal;

// TODO: Interface van maken zodat je m zelf kunt implementeren
//  (als Doctrine Entity bijvoorbeeld)
class Transaction
{
	private $transactionId;

	private $purchaseId;
	private $amount;
	private $description;
	private $expirationPeriod;
	private $entranceCode;
	private $language;
	private $currency;

	private $status;

	// TODO: Met interfaces etc een Transactie maken waarmee je de status op kunt vragen waar niet alle basic info ook weer in moet
	public function __construct($purchaseId, $amount, $description, \DateInterval $expirationPeriod = null, $entranceCode = null, $language = 'nl', $currency = 'EUR')
	{
		// TODO: Validation

		$this->transactionId = null;

		$this->purchaseId = $purchaseId;
		$this->amount = $amount;
		$this->description = $description;
		$this->expirationPeriod = $expirationPeriod;
		$this->entranceCode = $entranceCode;
		$this->language = $language;
		$this->currency = $currency;

		$this->status = null;
	}

	public function getTransactionId()
	{
		return $this->transactionId;
	}

	public function setTransactionId($transactionId)
	{
		$this->transactionId = $transactionId;
	}

	public function getPurchaseId()
	{
		return $this->purchaseId;
	}

	public function getAmount()
	{
		return $this->amount;
	}

	public function getDescription()
	{
		return $this->description;
	}

	public function getExpirationPeriod()
	{
		return $this->expirationPeriod;
	}

	public function getEntranceCode()
	{
		return $this->entranceCode;
	}

	public function getLanguage()
	{
		return $this->language;
	}

	public function getCurrency()
	{
		return $this->currency;
	}

	public function getStatus()
	{
		return $this->status;
	}

	public function setStatus($status)
	{
		$this->status = $status;
	}
}