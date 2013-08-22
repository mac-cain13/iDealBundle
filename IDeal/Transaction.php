<?php

namespace Wrep\IDealBundle\IDeal;

use Wrep\IDealBundle\IDeal\TransactionState\TransactionState;
use Wrep\IDealBundle\IDeal\TransactionState\TransactionStateNew;

// TODO: Interface van maken zodat je m zelf kunt implementeren
//  (als Doctrine Entity bijvoorbeeld)
class Transaction
{
	private $purchaseId;
	private $amount;
	private $description;
	private $expirationPeriod;
	private $entranceCode;
	private $language;
	private $currency;

	// TODO: Met interfaces etc een Transactie maken waarmee je de status op kunt vragen waar niet alle basic info ook weer in moet
	public function __construct($purchaseId, $amount, $description, \DateInterval $expirationPeriod = null, $entranceCode = null, $language = 'nl', $currency = 'EUR', TransactionState $initialState = null)
	{
		if (null == $initialState)
		{
			$initialState = new TransactionStateNew( new \DateTime() );
		}

		$this->setState($initialState);

		// TODO: Validation
		$this->purchaseId = $purchaseId;
		$this->amount = $amount;
		$this->description = $description;
		$this->expirationPeriod = $expirationPeriod;
		$this->entranceCode = $entranceCode;
		$this->language = $language;
		$this->currency = $currency;
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

	/*** State stuff starts here ***/
	public function getState()
	{
		return $this->state;
	}

	private function setState(TransactionState $state)
	{
		$this->state = $state;
	}

	public function getTimestamp()
	{
		return $this->getState()->getTimestamp();
	}

	public function getConsumer()
	{
		return $this->getState()->getConsumer();
	}

	public function setOpen(\DateTime $statusDateTimeStamp, $transactionId)
	{
		$this->status = $this->getState()->setOpen($statusDateTimeStamp, $transactionId);
	}

	public function setSuccess(\DateTime $statusDateTimeStamp, Consumer $consumer = null)
	{
		$this->status = $this->getState()->setSuccess($statusDateTimeStamp, $consumer);
	}

	public function setCancelled(\DateTime $statusDateTimeStamp)
	{
		$this->status = $this->getState()->setCancelled($statusDateTimeStamp);
	}

	public function setExpired(\DateTime $statusDateTimeStamp)
	{
		$this->status = $this->getState()->setExpired($statusDateTimeStamp);
	}

	public function setFailed(\DateTime $statusDateTimeStamp)
	{
		$this->status = $this->getState()->setFailed($statusDateTimeStamp);
	}
}
