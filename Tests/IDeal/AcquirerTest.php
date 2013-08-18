<?php

namespace Wrep\IDealBundle\Tests\IDeal;

use Wrep\IDealBundle\IDeal\Acquirer;
use Wrep\IDealBundle\Exception\InvalidArgumentException;

class AcquirerTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @dataProvider validConstructorData
	 */
	public function testConstruction($url, $certificatePath)
	{
		$acquirer = new Acquirer($url, $certificatePath);

		$this->assertInstanceOf('Wrep\IDealBundle\IDeal\Acquirer', $acquirer);
	}

	public function validConstructorData()
	{
		return array(
			array('https://idealtest.rabobank.nl/ideal/iDEALv3', __DIR__ . '/../Resources/acquirer-certificate.cer'),
			array('https://89.106.184.11/ideal/iDEALv3', __DIR__ . '/../Resources/acquirer-certificate.cer'),
			array('ssl://idealtest.rabobank.nl:443/ideal/iDEALv3', __DIR__ . '/../Resources/acquirer-certificate.cer'),
			array('ssl://89.106.184.11:443/ideal/iDEALv3', __DIR__ . '/../Resources/acquirer-certificate.cer'),
			);
	}

	/**
	 * @dataProvider invalidConstructorData
	 * @expectedException InvalidArgumentException
	 */
	public function testInvalidConstruction($url, $certificatePath)
	{
		new Acquirer($url, $certificatePath);
	}

	public function invalidConstructorData()
	{
		return array(
			array(null, __DIR__ . '/../Resources/acquirer-certificate.cer'),
			array('https://idealtest.rabobank.nl/ideal/iDEALv3', null),
			array('/ideal/iDEALv3', __DIR__ . '/../Resources/acquirer-certificate.cer'),
			array('//89.106.184.11:443/ideal/iDEALv3', __DIR__ . '/../Resources/acquirer-certificate.cer'),
			);
	}

	/**
	 * @dataProvider validConstructorData
	 */
	public function testGetUrl($url, $certificatePath)
	{
		$acquirer = new Acquirer($url, $certificatePath);
		$this->assertEquals($url, $acquirer->getUrl(), 'Acquirer URL getter returned unexpected URL.');
	}

	/**
	 * @dataProvider validConstructorData
	 */
	public function testGetCertificate($url, $certificatePath)
	{
		$acquirer = new Acquirer($url, $certificatePath);
		$this->assertEquals($certificatePath, $acquirer->getCertificate(), 'Acquirer certificate path getter returned unexpected value.');
	}
}
