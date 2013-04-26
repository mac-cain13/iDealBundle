<?php

namespace Wrep\IDealBundle\Tests\IDeal;

use \Wrep\IDealBundle\IDeal\IDealClient;

class IDealClientTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @dataProvider validContructorData
	 */
	public function testConstruction($merchantId, $merchantSubId, $merchantCertificate, $merchantCertificatePassphrase, $acquirerUrl, $acquirerCertificate, $acquirerTimeout)
	{
		$iDealClient = new IDealClient($merchantId, $merchantSubId, $merchantCertificate, $merchantCertificatePassphrase, $acquirerUrl, $acquirerCertificate, $acquirerTimeout);
		$this->assertEquals('Wrep\IDealBundle\IDeal\IDealClient', get_class($iDealClient), 'Constructed wrong IDealClient');
	}

	public function validContructorData()
	{
		return array(
				array(1, 0, __DIR__ . '/../Resources/merchant.pem', 'idealbundle', 'https://idealtest.rabobank.nl/ideal/iDEALv3', __DIR__ . '/../Resources/aquirer-certificate.cer', 5),
				array(123456789, 123456, __DIR__ . '/../Resources/merchant.pem', 'idealbundle', 'https://idealtest.rabobank.nl/ideal/iDEALv3', __DIR__ . '/../Resources/aquirer-certificate.cer', 15),
				array('012345678', 123456, __DIR__ . '/../Resources/merchant.pem', 'idealbundle', 'https://idealtest.rabobank.nl/ideal/iDEALv3', __DIR__ . '/../Resources/aquirer-certificate.cer', 15),
			);
	}

	/**
	 * @dataProvider invalidContructorData
	 */
	public function testInvalidConstruction($exceptionMessage, $merchantId, $merchantSubId, $merchantCertificate, $merchantCertificatePassphrase, $acquirerUrl, $acquirerCertificate, $acquirerTimeout)
	{
		$this->setExpectedException('\RuntimeException', $exceptionMessage);
		$iDealClient = new IDealClient($merchantId, $merchantSubId, $merchantCertificate, $merchantCertificatePassphrase, $acquirerUrl, $acquirerCertificate, $acquirerTimeout);
	}

	public function invalidContructorData()
	{
		return array(
				array('The merchant ID must a positive integer.', -1, 0, __DIR__ . '/../Resources/merchant.pem', 'idealbundle', 'https://idealtest.rabobank.nl/ideal/iDEALv3', __DIR__ . '/../Resources/aquirer-certificate.cer', 5),
				array('The merchant ID must a positive integer.', 'string', 0, __DIR__ . '/../Resources/merchant.pem', 'idealbundle', 'https://idealtest.rabobank.nl/ideal/iDEALv3', __DIR__ . '/../Resources/aquirer-certificate.cer', 5),
				array('The merchant ID must be 9 digits or less.', 1234567890, 0, __DIR__ . '/../Resources/merchant.pem', 'idealbundle', 'https://idealtest.rabobank.nl/ideal/iDEALv3', __DIR__ . '/../Resources/aquirer-certificate.cer', 5),
				array('The merchant subID must a positive integer.', 1, -1, __DIR__ . '/../Resources/merchant.pem', 'idealbundle', 'https://idealtest.rabobank.nl/ideal/iDEALv3', __DIR__ . '/../Resources/aquirer-certificate.cer', 5),
				array('The merchant subID must a positive integer.', 1, 'string', __DIR__ . '/../Resources/merchant.pem', 'idealbundle', 'https://idealtest.rabobank.nl/ideal/iDEALv3', __DIR__ . '/../Resources/aquirer-certificate.cer', 5),
				array('The merchant subID must be 6 digits or less.', 1, 1234567, __DIR__ . '/../Resources/merchant.pem', 'idealbundle', 'https://idealtest.rabobank.nl/ideal/iDEALv3', __DIR__ . '/../Resources/aquirer-certificate.cer', 5),
				array('The merchant certificate doesn\'t exists.', 1, 1, 'fakepath', 'idealbundle', 'https://idealtest.rabobank.nl/ideal/iDEALv3', __DIR__ . '/../Resources/aquirer-certificate.cer', 5),
				array('The acquirer certificate doesn\'t exists.', 1, 1, __DIR__ . '/../Resources/merchant.pem', 'idealbundle', 'https://idealtest.rabobank.nl/ideal/iDEALv3', 'fakepath', 5),
				array('The acquirer timout must be above zero.', 1, 1, __DIR__ . '/../Resources/merchant.pem', 'idealbundle', 'https://idealtest.rabobank.nl/ideal/iDEALv3', __DIR__ . '/../Resources/aquirer-certificate.cer', -1),
			);
	}
}
