<?php

namespace Wrep\IDealBundle\IDeal;

use Wrep\IDealBundle\Exception\InvalidArgumentException;

use Wrep\IDealBundle\IDeal\TransactionState\TransactionState;
use Wrep\IDealBundle\IDeal\TransactionState\TransactionStateNew;

class Transaction
{
	private $purchaseId;
	private $amount;
	private $description;
	private $expirationPeriod;
	private $entranceCode;
	private $language;
	private $currency;

	public function __construct($purchaseId, $amount, $description, \DateInterval $expirationPeriod = null, $entranceCode = null, TransactionState $initialState = null)
	{
		$this->setPurchaseId($purchaseId);
		$this->setAmount($amount);
		$this->setDescription($description);
		$this->setExpirationPeriod($expirationPeriod);
		$this->setEntranceCode($entranceCode);

		if (null == $initialState) {
			$initialState = new TransactionStateNew( new \DateTime() );
		}
		$this->setState($initialState);

		// Fixed values, iDEAL currently doesn't support anything else for these properties.
		$this->setLanguage('nl');
		$this->setCurrency('EUR');
	}

	public function getPurchaseId()
	{
		return $this->purchaseId;
	}

	protected function setPurchaseId($purchaseId)
	{
		if (!preg_match('/^([0-9a-z]){1,16}$/i', $purchaseId)) {
			throw new InvalidArgumentException('Purchase ID must be 1 to 16 characters and only letters/numbers. (' . $purchaseId . ')');
		}

		$this->purchaseId = $purchaseId;
	}

	public function getAmount()
	{
		return $this->amount;
	}

	protected function setAmount($amount)
	{
		if ( !(is_float($amount) && $amount > 0 && $amount <= 9999999999.99) ) {
			throw new InvalidArgumentException('Amount must be a double above 0 and below 1000000000.00. (' . $amount . ')');
		}

		$this->amount = $amount;
	}

	public function getDescription()
	{
		return $this->description;
	}

	protected function setDescription($description)
	{
		if (strlen($description) == 0 || strlen($description) > 32) {
			throw new InvalidArgumentException('Description must be 32 characters or less and cannot be empty. (' . $description . ')');
		}

		$this->description = $description;
	}

	public function getExpirationPeriod()
	{
		return $this->expirationPeriod;
	}

	protected function setExpirationPeriod(\DateInterval $expirationPeriod = null)
	{
		if ($expirationPeriod != null)
		{
			$dateTime = new \DateTime();
			$timestamp = $dateTime->getTimestamp();
			$intervalInSeconds = $dateTime->add($expirationPeriod)->getTimestamp() - $timestamp;

			if ($intervalInSeconds < 60 || $intervalInSeconds > 3600) {
				throw new InvalidArgumentException('Expiration period must be at least 1 minute and not more then 1 hour, 15 minutes is advised. (' . $intervalInSeconds . ' seconds)');
			}
		}

		$this->expirationPeriod = $expirationPeriod;
	}

	public function getEntranceCode()
	{
		return $this->entranceCode;
	}

	protected function setEntranceCode($entranceCode = null)
	{
		if ($entranceCode != null && !preg_match('/^([0-9a-z]){1,40}$/i', $entranceCode)) {
			throw new InvalidArgumentException('Entrance code must be 40 characters and only letters/numbers. (' . $entranceCode . ')');
		}

		$this->entranceCode = $entranceCode;
	}

	public function getLanguage()
	{
		return $this->language;
	}

	public function setLanguage($language)
	{
		// TODO: Should be validated, probably with a ISO639 object/class
		$this->language = $language;
	}

	public function getCurrency()
	{
		return $this->currency;
	}

	public function setCurrency($currency)
	{
		// TODO: Should be validated, probably with a ISO4217 object/class
		$this->currency = $currency;
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

	public function getStateTimestamp()
	{
		return $this->getState()->getTimestamp();
	}

	public function getTransactionId()
	{
		return $this->getState()->getTransactionId();
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
