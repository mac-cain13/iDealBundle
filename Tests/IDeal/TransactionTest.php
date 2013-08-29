<?php

namespace Wrep\IDealBundle\Tests\IDeal;

use Wrep\IDealBundle\IDeal\Transaction;
use Wrep\IDealBundle\IDeal\TransactionState\TransactionState;
use Wrep\IDealBundle\Exception\InvalidArgumentException;

class TransactionTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @dataProvider validConstructorData
	 */
	public function testConstruction($purchaseId, $amount, $description, \DateInterval $expirationPeriod = null, $entranceCode = null, TransactionState $initialState = null)
	{
		$transaction = new Transaction($purchaseId, $amount, $description, $expirationPeriod, $entranceCode, $initialState);

		$this->assertInstanceOf('Wrep\IDealBundle\IDeal\Transaction', $transaction);
	}

	public function validConstructorData()
	{
		$newState = $this->getMockBuilder('Wrep\IDealBundle\IDeal\TransactionState\TransactionStateNew')
							->disableOriginalConstructor()
							->getMock();
		$openState = $this->getMockBuilder('Wrep\IDealBundle\IDeal\TransactionState\TransactionStateOpen')
							->disableOriginalConstructor()
							->getMock();
		$successState = $this->getMockBuilder('Wrep\IDealBundle\IDeal\TransactionState\TransactionStateSuccess')
							->disableOriginalConstructor()
							->getMock();

		return array(
			array('1', 0.01, '1.000 Test credits', null, null, null),
			array('Id123', 0.01, '1.000 Test credits', null, null, null),

			array('1', 1.00, '1.000 Test credits', null, null, null),
			array('1', 10.50, '1.000 Test credits', null, null, null),
			array('1', 9999999999.99, '1.000 Test credits', null, null, null),

			array('1', 0.01, ' ', null, null, null),
			array('1', 0.01, '123456789 123456789 123456789 12', null, null, null),

			array('1', 0.01, '1.000 Test credits', new \DateInterval('PT1M'), null, null),
			array('1', 0.01, '1.000 Test credits', new \DateInterval('PT3M'), null, null),
			array('1', 0.01, '1.000 Test credits', new \DateInterval('PT15M'), null, null),
			array('1', 0.01, '1.000 Test credits', new \DateInterval('PT1H'), null, null),

			array('1', 0.01, '1.000 Test credits', null, 'a', null),
			array('1', 0.01, '1.000 Test credits', null, 'd131dd02c5e6eec4', null),
			array('1', 0.01, '1.000 Test credits', null, '123456789A123456789A123456789A123456789A', null),

			array('1', 0.01, '1.000 Test credits', null, null, $newState),
			array('1', 0.01, '1.000 Test credits', null, null, $openState),
			array('1', 0.01, '1.000 Test credits', null, null, $successState),
			);
	}

	/**
	 * @dataProvider invalidConstructorData
	 * @expectedException InvalidArgumentException
	 */
	public function testInvalidConstruction($purchaseId, $amount, $description, \DateInterval $expirationPeriod = null, $entranceCode = null, TransactionState $initialState = null)
	{
		$transaction = new Transaction($purchaseId, $amount, $description, $expirationPeriod, $entranceCode, $initialState);
	}

	public function invalidConstructorData()
	{
		return array(
			array(null, 0.01, '1.000 Test credits', null, null, null),
			array('-1', 0.01, '1.000 Test credits', null, null, null),
			array('a b', 0.01, '1.000 Test credits', null, null, null),
			array('#3', 0.01, '1.000 Test credits', null, null, null),

			array('1', -1.05, '1.000 Test credits', null, null, null),
			array('1', 0, '1.000 Test credits', null, null, null),
			array('1', 19999999999.99, '1.000 Test credits', null, null, null),
			array('1', null, '1.000 Test credits', null, null, null),

			array('1', 0.01, null, null, null, null),
			array('1', 0.01, '', null, null, null),
			array('1', 0.01, '123456789 123456789 123456789 123', null, null, null),

			array('1', 0.01, '1.000 Test credits', new \DateInterval('PT59S'), null, null),
			array('1', 0.01, '1.000 Test credits', new \DateInterval('PT1H1S'), null, null),
			array('1', 0.01, '1.000 Test credits', new \DateInterval('P1Y'), null, null),

			array('1', 0.01, '1.000 Test credits', null, '123456789A123456789A123456789A123456789Ab', null),
			array('1', 0.01, '1.000 Test credits', null, '123456789 123456789 123456789 123456789 ', null),
			);
	}

	/**
	 * @dataProvider validConstructorData
	 */
	// public function testGetId($id, $subId, $certificatePath, $certificatePassphrase)
	// {
	// 	$merchant = new Merchant($id, $subId, $certificatePath, $certificatePassphrase);
	// 	$this->assertEquals($id, $merchant->getId(), 'Merchant ID getter returned unexpected value.');
	// }
}
