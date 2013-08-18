<?php

namespace Wrep\IDealBundle\IDeal\TransactionState;

use Wrep\IDealBundle\IDeal\Consumer;
use Wrep\IDealBundle\Exception\LogicException;

class TransactionStateNew implements TransactionState
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

	public function getConsumer()
	{
		return null;
	}

	public function __toString()
	{
		return TransactionState::STATE_NEW;
	}

	public function setOpen(\DateTime $timestamp, $transactionId)
	{
		return new TransactionStateOpen($timestamp);
	}

	public function setSuccess(\DateTime $timestamp, Consumer $consumer = null)
	{
		throw new LogicException('Cannot transition a Transaction from state ' . (string)$this . ' to ' . TransactionState::STATE_SUCCESS . '.');
	}

	public function setCancelled(\DateTime $timestamp)
	{
		throw new LogicException('Cannot transition a Transaction from state ' . (string)$this . ' to ' . TransactionState::STATE_CANCELLED . '.');
	}

	public function setExpired(\DateTime $timestamp)
	{
		throw new LogicException('Cannot transition a Transaction from state ' . (string)$this . ' to ' . TransactionState::STATE_EXPIRED . '.');
	}

	public function setFailed(\DateTime $timestamp)
	{
		throw new LogicException('Cannot transition a Transaction from state ' . (string)$this . ' to ' . TransactionState::STATE_FAILED . '.');
	}
}
