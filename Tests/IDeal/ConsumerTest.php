<?php

namespace Wrep\IDealBundle\Tests\IDeal;

use Wrep\IDealBundle\IDeal\Consumer;
use Wrep\IDealBundle\IDeal\BIC;
use Wrep\IDealBundle\Exception\InvalidArgumentException;

class ConsumerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @dataProvider validConstructorData
	 */
	public function testConstruction($name, $iban, BIC $bic = null)
	{
		$consumer = new Consumer($name, $iban, $bic);

		$this->assertInstanceOf('Wrep\IDealBundle\IDeal\Consumer', $consumer);
	}

	public function validConstructorData()
	{
		$bic = $this->getMockBuilder('Wrep\IDealBundle\IDeal\BIC')
					->disableOriginalConstructor()
					->getMock();

		return array(
			array('Mathijs Kadijk', 'NL91ABNA0417164300', $bic),
			array('Mr. Malta', 'MT84MALT011000012345MTLCAST001S', $bic),

			array(null, 'NL91ABNA0417164300', $bic),
			array('Mathijs Kadijk', null, $bic),
			array('Mathijs Kadijk', 'NL91ABNA0417164300', null),

			array(null, null, $bic),
			array(null, 'NL91ABNA0417164300', null),
			array('Mathijs Kadijk', null, null),

			array('', 'NL91ABNA0417164300', $bic),
			array('Mathijs Kadijk', '', $bic),
			array('Mathijs Kadijk', 'NL91ABNA0417164300', null),

			array(null, null, null),
			);
	}

	/**
	 * @dataProvider invalidConstructorData
	 * @expectedException InvalidArgumentException
	 */
	public function testInvalidConstruction($name, $iban, BIC $bic = null)
	{
		new Consumer($name, $iban, $bic);
	}

	public function invalidConstructorData()
	{
		$bic = $this->getMockBuilder('Wrep\IDealBundle\IDeal\BIC')
					->disableOriginalConstructor()
					->getMock();

		return array(
			array(new \StdClass(), null, null),
			array(null, new \StdClass(), null),
			array(array(), null, null),
			array(null, array(), null),
			);
	}

	/**
	 * @dataProvider validConstructorData
	 */
	public function testGetName($name, $iban, BIC $bic = null)
	{
		$consumer = new Consumer($name, $iban, $bic);

		$this->assertEquals($name, $consumer->getName());
	}

	/**
	 * @dataProvider validConstructorData
	 */
	public function testGetIban($name, $iban, BIC $bic = null)
	{
		$consumer = new Consumer($name, $iban, $bic);

		$this->assertEquals($iban, $consumer->getIban());
	}

	/**
	 * @dataProvider validConstructorData
	 */
	public function testGetBIC($name, $iban, BIC $bic = null)
	{
		$consumer = new Consumer($name, $iban, $bic);

		$this->assertEquals($bic, $consumer->getBIC());
	}

	/**
	 * @dataProvider validConstructorData
	 */
	public function testIsEmpty($name, $iban, BIC $bic = null)
	{
		$consumer = new Consumer($name, $iban, $bic);

		$this->assertEquals( (empty($name) && empty($iban) && empty($bic)), $consumer->isEmpty());
	}
}
