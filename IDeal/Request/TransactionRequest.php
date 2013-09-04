<?php

namespace Wrep\IDealBundle\IDeal\Request;

use Wrep\IDealBundle\IDeal\Merchant;
use Wrep\IDealBundle\IDeal\Transaction;
use Wrep\IDealBundle\IDeal\IssuerId;

class TransactionRequest extends BaseRequest
{
	public function __construct(Merchant $merchant, Transaction $transaction, IssuerId $issuer, $returnUrl)
	{
		parent::__construct(BaseRequest::TYPE_TRANSACTION, $merchant);
		$this->setTransaction($transaction);
		$this->setIssuer($issuer);
		$this->setReturnUrl($returnUrl);
	}
}
