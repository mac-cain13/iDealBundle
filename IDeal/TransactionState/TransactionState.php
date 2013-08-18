<?php

namespace Wrep\IDealBundle\IDeal\TransactionState;

use Wrep\IDealBundle\IDeal\Consumer;

interface TransactionState
{
	const STATE_NEW = 'New';
	const STATE_OPEN = 'Open';
	const STATE_SUCCESS = 'Success';
	const STATE_CANCELLED = 'Cancelled';
	const STATE_EXPIRED = 'Expired';
	const STATE_FAILURE = 'Failure';

	public function __construct(\DateTime $timestamp);

	public function getTimestamp();
	public function getConsumer();
	public function __toString();

	public function setOpen(\DateTime $timestamp, $transactionId);
	public function setSuccess(\DateTime $timestamp, Consumer $consumer = null);
	public function setCancelled(\DateTime $timestamp);
	public function setExpired(\DateTime $timestamp);
	public function setFailed(\DateTime $timestamp);
}
