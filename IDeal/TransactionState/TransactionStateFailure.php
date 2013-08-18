<?php

namespace Wrep\IDealBundle\IDeal\TransactionState;

class TransactionStateFailure extends TransactionStateFinal
{
	public function __toString()
	{
		return TransactionState::STATE_FAILURE;
	}
}
