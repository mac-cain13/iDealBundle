<?php

namespace Wrep\IDealBundle\Tests\IDeal;

use Wrep\IDealBundle\IDeal\Merchant;
use Wrep\IDealBundle\Exception\InvalidArgumentException;

class MerchantTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @dataProvider validConstructorData
	 */
	public function testConstruction($id, $subId, $certificatePath, $certificatePassphrase)
	{
		$merchant = new Merchant($id, $subId, $certificatePath, $certificatePassphrase);

		$this->assertInstanceOf('Wrep\IDealBundle\IDeal\Merchant', $merchant);
	}

	public function validConstructorData()
	{
		return array(
			array(1, 0, __DIR__ . '/../Resources/merchant.pem', null),
			array(123456789, 123456, __DIR__ . '/../Resources/merchant.pem', 'password'),
			array('123456789', '123456', __DIR__ . '/../Resources/merchant.pem', 'password'),
			);
	}

	/**
	 * @dataProvider invalidConstructorData
	 * @expectedException InvalidArgumentException
	 */
	public function testInvalidConstruction($id, $subId, $certificatePath, $certificatePassphrase)
	{
		new Merchant($id, $subId, $certificatePath, $certificatePassphrase);
	}

	public function invalidConstructorData()
	{
		return array(
			array(-1, 0, __DIR__ . '/../Resources/merchant.pem', null),
			array(0, 0, __DIR__ . '/../Resources/merchant.pem', null),
			array(1234567890, 0, __DIR__ . '/../Resources/merchant.pem', 'password'),

			array(1, -1, __DIR__ . '/../Resources/merchant.pem', null),
			array(1, 1234567, __DIR__ . '/../Resources/merchant.pem', 'password'),

			array(1, 0, __DIR__ . '/../Resources/merchant_doesnt_exist.pem', null),
			);
	}

	/**
	 * @dataProvider validConstructorData
	 */
	public function testGetId($id, $subId, $certificatePath, $certificatePassphrase)
	{
		$merchant = new Merchant($id, $subId, $certificatePath, $certificatePassphrase);
		$this->assertEquals($id, $merchant->getId(), 'Merchant ID getter returned unexpected value.');
	}

	/**
	 * @dataProvider validConstructorData
	 */
	public function testGetSubId($id, $subId, $certificatePath, $certificatePassphrase)
	{
		$merchant = new Merchant($id, $subId, $certificatePath, $certificatePassphrase);
		$this->assertEquals($subId, $merchant->getSubId(), 'Merchant SubID getter returned unexpected value.');
	}

	/**
	 * @dataProvider validConstructorData
	 */
	public function testGetCertificate($id, $subId, $certificatePath, $certificatePassphrase)
	{
		$merchant = new Merchant($id, $subId, $certificatePath, $certificatePassphrase);
		$this->assertEquals($certificatePath, $merchant->getCertificate(), 'Merchant certificate path getter returned unexpected value.');
	}

	/**
	 * @dataProvider validConstructorData
	 */
	public function testGetCertificatePassphrase($id, $subId, $certificatePath, $certificatePassphrase)
	{
		$merchant = new Merchant($id, $subId, $certificatePath, $certificatePassphrase);
		$this->assertEquals($certificatePassphrase, $merchant->getCertificatePassphrase(), 'Merchant certificate passphrase getter returned unexpected value.');
	}
}
