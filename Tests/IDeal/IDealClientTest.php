<?php

namespace Wrep\IDealBundle\Tests\IDeal;

use \Wrep\IDealBundle\IDeal\IDealClient;

class IDealClientTest extends \PHPUnit_Framework_TestCase
{
	/**
	 * @dataProvider validContructorData
	 */
	public function testConstructor($merchantId, $merchantSubId, $merchantCertificate, $merchantCertificatePassphrase, $acquirerUrl, $acquirerCertificate, $acquirerTimeout)
	{
		$iDealClient = new IDealClient($merchantId, $merchantSubId, $merchantCertificate, $merchantCertificatePassphrase, $acquirerUrl, $acquirerCertificate, $acquirerTimeout);
		$this->assertEquals('Wrep\IDealBundle\IDeal\IDealClient', get_class($iDealClient), 'Constructed wrong IDealClient');
	}

	public function validContructorData()
	{
		return array(
				array(1234, 0, 'woei', 'passphrase', 'randomUrl', 'aqCert', 5)
			);
	}

	public function testFetchIssuerList()
	{
		$iDealClient = new IDealClient(1, 0, __FILE__, $merchantCertificatePassphrase, 'URL', __FILE__);
		$iDealClient->fetchIssuerList();
	}
}
