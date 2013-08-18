<?php

namespace Wrep\IDealBundle\Tests\IDeal;

use Wrep\IDealBundle\IDeal\Issuer;
use Wrep\IDealBundle\Exception\InvalidArgumentException;

class IssuerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @dataProvider validConstructorData
	 */
	public function testConstruction($bic, $name)
	{
		$issuer = new Issuer($bic, $name);

		$this->assertInstanceOf('Wrep\IDealBundle\IDeal\Issuer', $issuer);
	}

	public function validConstructorData()
	{
		$bic = $this->getMockBuilder('Wrep\IDealBundle\IDeal\BIC')
					->disableOriginalConstructor()
					->getMock();

		return array(
			array($bic, 'Rabobank'),
			array($bic, 'ABN Amro'),
			);
	}

	/**
	 * @dataProvider invalidConstructorData
	 * @expectedException InvalidArgumentException
	 */
	public function testInvalidConstruction($bic, $name)
	{
		$issuer = new Issuer($bic, $name);

		$this->assertInstanceOf('Wrep\IDealBundle\IDeal\Issuer', $issuer);
	}

	public function invalidConstructorData()
	{
		$bic = $this->getMockBuilder('Wrep\IDealBundle\IDeal\BIC')
					->disableOriginalConstructor()
					->getMock();

		return array(
			//array(null, 'Rabobank'), // This is enforced by PHP self and throws a PHPUnit_Framework_Error
			array($bic, null),
			array($bic, ''),
			);
	}

	/**
	 * @dataProvider validConstructorData
	 */
	public function testGetName($bic, $name)
	{
		$issuer = new Issuer($bic, $name);
		$this->assertEquals($name, $issuer->getName(), 'Issuer name getter returned unexpected value.');
	}

	/**
	 * @dataProvider validConstructorData
	 */
	public function testGetBIC($bic, $name)
	{
		$issuer = new Issuer($bic, $name);
		$this->assertEquals($bic, $issuer->getBIC(), 'Issuer BIC getter returned unexpected value.');
	}
}
