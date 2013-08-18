<?php

namespace Wrep\IDealBundle\IDeal\TransactionState;

class TransactionStateCancelled extends TransactionStateFinal
{
	public function __toString()
	{
		return TransactionState::STATE_CANCELLED;
	}
}
