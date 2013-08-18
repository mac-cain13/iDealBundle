<?php

namespace Wrep\IDealBundle\IDeal\TransactionState;

class TransactionStateExpired extends TransactionStateFinal
{
	public function __toString()
	{
		return TransactionState::STATE_EXPIRED;
	}
}
