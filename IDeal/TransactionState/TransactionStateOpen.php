<?php

namespace Wrep\IDealBundle\IDeal\TransactionState;

use Wrep\IDealBundle\IDeal\Consumer;
use Wrep\IDealBundle\Exception\LogicException;

class TransactionStateOpen implements TransactionState
{
	private $timestamp;
	private $transactionId;

	public function __construct(\DateTime $timestamp, $transactionId)
	{
		$this->timestamp = $timestamp;
		$this->transactionId = $transactionId;
	}

	public function getTimestamp()
	{
		return $this->timestamp;
	}

	public function getTransactionId()
	{
		return $this->transactionId;
	}

	public function getConsumer()
	{
		return null;
	}

	public function __toString()
	{
		return TransactionState::STATE_OPEN;
	}

	public function setOpen(\DateTime $timestamp, $transactionId)
	{
		throw new LogicException('Cannot transition a Transaction from state ' . (string)$this . ' to ' . TransactionState::STATE_OPEN . '.');
	}

	public function setSuccess(\DateTime $timestamp, Consumer $consumer = null)
	{
		return new TransactionStateSuccess($timestamp, $consumer);
	}

	public function setCancelled(\DateTime $timestamp)
	{
		return new TransactionStateCancelled($timestamp);
	}

	public function setExpired(\DateTime $timestamp)
	{
		return new TransactionStateExpired($timestamp);
	}

	public function setFailed(\DateTime $timestamp)
	{
		return new TransactionStateFailed($timestamp);
	}
}
