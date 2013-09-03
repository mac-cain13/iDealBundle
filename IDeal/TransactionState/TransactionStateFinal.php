<?php

namespace Wrep\IDealBundle\IDeal\TransactionState;

use Wrep\IDealBundle\IDeal\Consumer;
use Wrep\IDealBundle\Exception\LogicException;

abstract class TransactionStateFinal implements TransactionState
{
	private $timestamp;

	public function __construct(\DateTime $timestamp)
	{
		$this->timestamp = $timestamp;
	}

	public function getTimestamp()
	{
		return $this->timestamp;
	}

	public function getTransactionId()
	{
		return null;
	}

	public function getConsumer()
	{
		return null;
	}

	public function setOpen(\DateTime $timestamp, $transactionId)
	{
		throw new LogicException('Cannot transition a Transaction from final state ' . (string)$this . ' to any other state.');
	}

	public function setSuccess(\DateTime $timestamp, Consumer $consumer = null)
	{
		throw new LogicException('Cannot transition a Transaction from final state ' . (string)$this . ' to any other state.');
	}

	public function setCancelled(\DateTime $timestamp)
	{
		throw new LogicException('Cannot transition a Transaction from final state ' . (string)$this . ' to any other state.');
	}

	public function setExpired(\DateTime $timestamp)
	{
		throw new LogicException('Cannot transition a Transaction from final state ' . (string)$this . ' to any other state.');
	}

	public function setFailed(\DateTime $timestamp)
	{
		throw new LogicException('Cannot transition a Transaction from final state ' . (string)$this . ' to any other state.');
	}
}
