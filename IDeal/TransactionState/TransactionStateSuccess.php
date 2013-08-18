<?php

namespace Wrep\IDealBundle\IDeal\TransactionState;

class TransactionStateSuccess extends TransactionStateFinal
{
	private $consumer;

	public function __construct(\DateTime $timestamp, Consumer $consumer = null)
	{
		parent::__construct($timestamp);

		$this->consumer = $consumer;
	}

	public function getConsumer()
	{
		return $this->consumer;
	}

	public function __toString()
	{
		return TransactionState::STATE_SUCCESS;
	}
}
