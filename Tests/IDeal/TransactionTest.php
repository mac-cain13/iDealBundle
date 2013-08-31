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
	public function testGetPurchaseId($purchaseId, $amount, $description, \DateInterval $expirationPeriod = null, $entranceCode = null, TransactionState $initialState = null)
	{
		$transaction = new Transaction($purchaseId, $amount, $description, $expirationPeriod, $entranceCode, $initialState);
		$this->assertEquals($purchaseId, $transaction->getPurchaseId(), 'Transaction Purchase ID getter returned unexpected value.');
	}

	/**
	 * @dataProvider validConstructorData
	 */
	public function testGetAmount($purchaseId, $amount, $description, \DateInterval $expirationPeriod = null, $entranceCode = null, TransactionState $initialState = null)
	{
		$transaction = new Transaction($purchaseId, $amount, $description, $expirationPeriod, $entranceCode, $initialState);
		$this->assertEquals($amount, $transaction->getAmount(), 'Transaction amount getter returned unexpected value.');
	}

	/**
	 * @dataProvider validConstructorData
	 */
	public function testGetDescription($purchaseId, $amount, $description, \DateInterval $expirationPeriod = null, $entranceCode = null, TransactionState $initialState = null)
	{
		$transaction = new Transaction($purchaseId, $amount, $description, $expirationPeriod, $entranceCode, $initialState);
		$this->assertEquals($description, $transaction->getDescription(), 'Transaction description getter returned unexpected value.');
	}

	/**
	 * @dataProvider validConstructorData
	 */
	public function testGetEntranceCode($purchaseId, $amount, $description, \DateInterval $expirationPeriod = null, $entranceCode = null, TransactionState $initialState = null)
	{
		$transaction = new Transaction($purchaseId, $amount, $description, $expirationPeriod, $entranceCode, $initialState);
		$this->assertEquals($entranceCode, $transaction->getEntranceCode(), 'Transaction entrance code getter returned unexpected value.');
	}

	/*** State related tests ***/

	/**
	 * @dataProvider validConstructorData
	 */
	public function testGetState($purchaseId, $amount, $description, \DateInterval $expirationPeriod = null, $entranceCode = null, TransactionState $initialState = null)
	{
		$transaction = new Transaction($purchaseId, $amount, $description, $expirationPeriod, $entranceCode, $initialState);

		if ($initialState != null) {
			$this->assertEquals($initialState, $transaction->getState(), 'Transaction state getter returned unexpected value.');
		} else {
			$this->assertInstanceOf('Wrep\IDealBundle\IDeal\TransactionState\TransactionStateNew', $transaction->getState());
		}
	}

	public function testGetStateTimestamp()
	{
		$mockState = $this->getMockBuilder('Wrep\IDealBundle\IDeal\TransactionState\TransactionStateSuccess')
							->disableOriginalConstructor()
							->getMock();

		$timestamp = new \DateTime();

		$mockState->expects($this->once())
					->method('getTimestamp')
					->will( $this->returnValue($timestamp) );

		$transaction = new Transaction('1', 0.01, '1.000 Test credits', null, null, $mockState);
		$this->assertEquals($timestamp, $transaction->getStateTimestamp(), 'Transaction timestamp getter returned unexpected value.');
	}

	public function testGetTransactionId()
	{
		$mockState = $this->getMockBuilder('Wrep\IDealBundle\IDeal\TransactionState\TransactionStateSuccess')
							->disableOriginalConstructor()
							->getMock();

		$transactionId = 123;

		$mockState->expects($this->once())
					->method('getTransactionId')
					->will( $this->returnValue($transactionId) );

		$transaction = new Transaction('1', 0.01, '1.000 Test credits', null, null, $mockState);
		$this->assertEquals($transactionId, $transaction->getTransactionId(), 'Transaction transaction ID getter returned unexpected value.');
	}

	public function testGetConsumer()
	{
		$mockState = $this->getMockBuilder('Wrep\IDealBundle\IDeal\TransactionState\TransactionStateSuccess')
							->disableOriginalConstructor()
							->getMock();

		$consumer = $this->getMockBuilder('Wrep\IDealBundle\IDeal\Consumer')
							->disableOriginalConstructor()
							->getMock();

		$mockState->expects($this->once())
					->method('getConsumer')
					->will( $this->returnValue($consumer) );

		$transaction = new Transaction('1', 0.01, '1.000 Test credits', null, null, $mockState);
		$this->assertEquals($consumer, $transaction->getConsumer(), 'Transaction consumer getter returned unexpected value.');
	}
}
