<?php

namespace Wrep\IDealBundle\Tests\IDeal;

use \Wrep\IDealBundle\IDeal\Request;

class RequestTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @dataProvider validContructorData
	 */
	public function testConstruction($rootElement, $merchantCertificate, $merchantCertificatePassphrase)
	{
		$request = new Request($rootElement, $merchantCertificate, $merchantCertificatePassphrase);
		$this->assertEquals('Wrep\IDealBundle\IDeal\Request', get_class($request), 'Constructed wrong class');
	}

	public function validContructorData()
	{
		return array(
				array('DirectoryReq', __DIR__ . '/../Resources/merchant.pem', 'idealbundle'),
				array('AcquirerTrxReq', __DIR__ . '/../Resources/merchant.pem', 'idealbundle'),
				array('AcquirerStatusReq', __DIR__ . '/../Resources/merchant.pem', 'idealbundle'),
				array(Request::TYPE_DIRECTORY, __DIR__ . '/../Resources/merchant.pem', 'idealbundle'),
				array(Request::TYPE_TRANSACTION, __DIR__ . '/../Resources/merchant.pem', 'idealbundle'),
				array(Request::TYPE_STATUS, __DIR__ . '/../Resources/merchant.pem', 'idealbundle'),
			);
	}

	/**
	 * @dataProvider invalidContructorData
	 */
	public function testInvalidConstruction($exceptionMessage, $rootElement, $merchantCertificate, $merchantCertificatePassphrase)
	{
		$this->setExpectedException('\RuntimeException', $exceptionMessage);
		$request = new Request($rootElement, $merchantCertificate, $merchantCertificatePassphrase);
	}

	public function invalidContructorData()
	{
		return array(
				array('The merchant certificate doesn\'t exists.', Request::TYPE_DIRECTORY, 'fakecertificate.pem', 'idealbundle'),
				array('No request type given.', null, __DIR__ . '/../Resources/merchant.pem', 'idealbundle'),
				array('Request type must be alphanumeric.', '<DirectoryReq>', __DIR__ . '/../Resources/merchant.pem', 'idealbundle'),
			);
	}

	/**
	 * @dataProvider invalidMerchantData
	 */
	public function testInvalidAddMerchant($exceptionMessage, $merchantId, $merchantSubId, $merchantReturnURL)
	{
		$this->setExpectedException('\RuntimeException', $exceptionMessage);
		$request = new Request(Request::TYPE_DIRECTORY, __DIR__ . '/../Resources/merchant.pem', 'idealbundle');
		$request->addMerchant($merchantId, $merchantSubId, $merchantReturnURL);
	}

	public function invalidMerchantData()
	{
		return array(
				array('The merchant ID must a positive integer.', -1, 0, null),
				array('The merchant ID must a positive integer.', 'string', 0, null),
				array('The merchant ID must be 9 digits or less.', 1234567890, 0, null),
				array('The merchant subID must a positive integer.', 1, -1, null),
				array('The merchant subID must a positive integer.', 1, 'string', null),
				array('The merchant subID must be 6 digits or less.', 1, 1234567, null),
				array('The merchant return URL must be a string of 512 characters or less.', 1, 123456, 'v9CKVBH9FoJywo6W4DrEYA1fDBoZdIKzYRJlpZ2ILNRUMgxoN2lCNNQpgVfMlsPLUWxBXcm7LMGBFaNUjviou7TajlKdVj14AnCciQFXCXilubqHQSlgHYOLJR0gqobzXSmA6i2ZzOKOphaEtwoLDMz6x25nyggkEE159lMqDTFQfWCC7ny4n6zbr2djyWNIBr6ZmmW3y0xkDrAEpfcVfGVPuOEgjs8zoqSvkYFgQrfmtDQQVXmDuTTe0rxkSB82yXjLFXDRo3MQ3fdluL9ghw5ngSkBn3kkvHJVGlJVkUlRwuZsr1FMCLR8a47Ni7OCC85vrX05jodK0FnKb0y2zczzIfufQ8NH2LR2EqY0FdH1WweHgAmCSgqDKofbIMkHgh1iURj9T1ase0of9r2pIj6oIAUAIWcDvgFmLbQeVIfnKxmsSla0YnKMLITKpPtvE2sMqN2kNHxpcXf3LW90DgMxNd6gNuNyWqgCc6ZH6EhiAJxWGNTUXRFIhA1GzZfEr'),
			);
	}
}
