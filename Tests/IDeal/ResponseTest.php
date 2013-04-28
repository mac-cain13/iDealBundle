<?php

namespace Wrep\IDealBundle\Tests\IDeal;

use \Wrep\IDealBundle\IDeal\Response;

class ResponseTest extends \PHPUnit_Framework_TestCase
{
	private $response;

	public function setUp()
	{
		$this->response = new Response('<?xml version="1.0" encoding="UTF-8"?><DirectoryRes xmlns="http://www.idealdesk.com/ideal/messages/mer-acq/3.3.1" xmlns:ns2="http://www.w3.org/2000/09/xmldsig#" version="3.3.1">
    <createDateTimestamp>2013-04-28T19:50:23.816Z</createDateTimestamp>
    <Acquirer>
        <acquirerID>0020</acquirerID>
    </Acquirer>
    <Directory>
        <directoryDateTimestamp>2013-04-28T19:50:23.816Z</directoryDateTimestamp>
        <Country>
            <countryNames>Deutschland</countryNames>
            <Issuer>
                <issuerID>INGBNL2A</issuerID>
                <issuerName>Issuer Simulation V3 - ING</issuerName>
            </Issuer>
            <Issuer>
                <issuerID>RABONL2U</issuerID>
                <issuerName>Issuer Simulation V3 - RABO</issuerName>
            </Issuer>
        </Country>
    </Directory>
<Signature xmlns="http://www.w3.org/2000/09/xmldsig#"><SignedInfo><CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/><SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"/><Reference URI=""><Transforms><Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"/></Transforms><DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/><DigestValue>SuUFWbDZsYNzJEHtIHXpF/P97PXt4Pt7baHzSsLv+2g=</DigestValue></Reference></SignedInfo><SignatureValue>REHeLr3bK6bf4w2grW2n/pWzrwP9HgBYo5CU6N7FJB+GzwrX20OwmgRsY8bKB8S1Kv6eIshK2d6m
zIsRp2pJP5O/BhkUWbpaQmOT+bw35d6RcHuzrFGah1IbzdwL57Rr5IG28tLSJYXOXKkQ+ZlLWKZS
0k8tm+CfJVrwRLmttjq4iVKOnGTNtaIMPA8nGeZOCqiXbUs1cGzOlOAWffpILGmhqwnCe/fgHjO4
zzlNrDZ+npjGy1HsdVPVknS365B1YQMSRam7kX4HuxzfewUnWHpMfrpZfRR8VivMQNCeBb0s/osS
1tM1ne3QeJa8Mb9npFz9ZdJaenzOzcFY8NV74A==</SignatureValue><KeyInfo><KeyName>FC0A17A7ABD72369726EA4D4DBEF9838128A7C78</KeyName></KeyInfo></Signature></DirectoryRes>', __DIR__ . '/../Resources/aquirer-certificate.cer');
	}

	/**
	 * @dataProvider invalidContructorData
	 */
	public function testInvalidConstruction($exceptionMessage, $xml, $acquirerCertificate, $exceptionClass = 'Wrep\IDealBundle\Exception\IDealException')
	{
		$this->setExpectedException($exceptionClass, $exceptionMessage);
		new Response($xml, $acquirerCertificate);
	}

	public function invalidContructorData()
	{
		return array(
				array('Invalid response XML:', null, __DIR__ . '/../Resources/aquirer-certificate.cer', 'RuntimeException'),
				array('Failed to verify response signature: No signature found in response.', '<?xml version="1.0" encoding="UTF-8"?><RandomXML />', __DIR__ . '/../Resources/aquirer-certificate.cer'),
				array('The acquirer certificate doesn\'t exists.', '<?xml version="1.0" encoding="UTF-8"?><DirectoryRes xmlns="http://www.idealdesk.com/ideal/messages/mer-acq/3.3.1" xmlns:ns2="http://www.w3.org/2000/09/xmldsig#" version="3.3.1">
    <createDateTimestamp>2013-04-28T19:50:23.816Z</createDateTimestamp>
    <Acquirer>
        <acquirerID>0020</acquirerID>
    </Acquirer>
    <Directory>
        <directoryDateTimestamp>2013-04-28T19:50:23.816Z</directoryDateTimestamp>
        <Country>
            <countryNames>Deutschland</countryNames>
            <Issuer>
                <issuerID>INGBNL2A</issuerID>
                <issuerName>Issuer Simulation V3 - ING</issuerName>
            </Issuer>
            <Issuer>
                <issuerID>RABONL2U</issuerID>
                <issuerName>Issuer Simulation V3 - RABO</issuerName>
            </Issuer>
        </Country>
    </Directory>
<Signature xmlns="http://www.w3.org/2000/09/xmldsig#"><SignedInfo><CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/><SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"/><Reference URI=""><Transforms><Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"/></Transforms><DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/><DigestValue>SuUFWbDZsYNzJEHtIHXpF/P97PXt4Pt7baHzSsLv+2g=</DigestValue></Reference></SignedInfo><SignatureValue>REHeLr3bK6bf4w2grW2n/pWzrwP9HgBYo5CU6N7FJB+GzwrX20OwmgRsY8bKB8S1Kv6eIshK2d6m
zIsRp2pJP5O/BhkUWbpaQmOT+bw35d6RcHuzrFGah1IbzdwL57Rr5IG28tLSJYXOXKkQ+ZlLWKZS
0k8tm+CfJVrwRLmttjq4iVKOnGTNtaIMPA8nGeZOCqiXbUs1cGzOlOAWffpILGmhqwnCe/fgHjO4
zzlNrDZ+npjGy1HsdVPVknS365B1YQMSRam7kX4HuxzfewUnWHpMfrpZfRR8VivMQNCeBb0s/osS
1tM1ne3QeJa8Mb9npFz9ZdJaenzOzcFY8NV74A==</SignatureValue><KeyInfo><KeyName>FC0A17A7ABD72369726EA4D4DBEF9838128A7C78</KeyName></KeyInfo></Signature></DirectoryRes>', null, 'RuntimeException'),
				array('Failed to verify response signature: Signature invalid', '<?xml version="1.0" encoding="UTF-8"?><DirectoryRes xmlns="http://www.idealdesk.com/ideal/messages/mer-acq/3.3.1" xmlns:ns2="http://www.w3.org/2000/09/xmldsig#" version="3.3.1">
    <createDateTimestamp>2013-04-28T19:50:23.816Z</createDateTimestamp>
    <Acquirer>
        <acquirerID>0020</acquirerID>
    </Acquirer>
    <Directory>
        <directoryDateTimestamp>2013-04-28T19:50:23.816Z</directoryDateTimestamp>
        <Country>
            <countryNames>Deutschland</countryNames>
            <Issuer>
                <issuerID>INGBNL2A</issuerID>
                <issuerName>Issuer Simulation V3 - ING</issuerName>
            </Issuer>
            <Issuer>
                <issuerID>RABONL2U</issuerID>
                <issuerName>Issuer Simulation V3 - RABO</issuerName>
            </Issuer>
        </Country>
    </Directory>
<Signature xmlns="http://www.w3.org/2000/09/xmldsig#"><SignedInfo><CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/><SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"/><Reference URI=""><Transforms><Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"/></Transforms><DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/><DigestValue>SuUFWbDZsYNzJEHtIHXpF/P97PXt4Pt7baHzSsLv+2g=</DigestValue></Reference></SignedInfo><SignatureValue>INVALID</SignatureValue><KeyInfo><KeyName>FC0A17A7ABD72369726EA4D4DBEF9838128A7C78</KeyName></KeyInfo></Signature></DirectoryRes>', __DIR__ . '/../Resources/aquirer-certificate.cer'),
				array('Failed to verify response signature: Reference validation failed', '<?xml version="1.0" encoding="UTF-8"?><DirectoryRes xmlns="http://www.idealdesk.com/ideal/messages/mer-acq/3.3.1" xmlns:ns2="http://www.w3.org/2000/09/xmldsig#" version="3.3.1">
    <createDateTimestamp>2013-04-28T19:50:23.816Z</createDateTimestamp>
    <Acquirer>
        <acquirerID>0020</acquirerID>
    </Acquirer>
    <Directory>
        <directoryDateTimestamp>2013-04-28T19:50:23.816Z</directoryDateTimestamp>
        <Country>
            <countryNames>Deutschland</countryNames>
            <Issuer>
                <issuerID>INGBNL2A</issuerID>
                <issuerName>Issuer Simulation V3 - ING</issuerName>
            </Issuer>
            <Issuer>
                <issuerID>RABONL2U</issuerID>
                <issuerName>INVALID</issuerName>
            </Issuer>
        </Country>
    </Directory>
<Signature xmlns="http://www.w3.org/2000/09/xmldsig#"><SignedInfo><CanonicalizationMethod Algorithm="http://www.w3.org/2001/10/xml-exc-c14n#"/><SignatureMethod Algorithm="http://www.w3.org/2001/04/xmldsig-more#rsa-sha256"/><Reference URI=""><Transforms><Transform Algorithm="http://www.w3.org/2000/09/xmldsig#enveloped-signature"/></Transforms><DigestMethod Algorithm="http://www.w3.org/2001/04/xmlenc#sha256"/><DigestValue>SuUFWbDZsYNzJEHtIHXpF/P97PXt4Pt7baHzSsLv+2g=</DigestValue></Reference></SignedInfo><SignatureValue>REHeLr3bK6bf4w2grW2n/pWzrwP9HgBYo5CU6N7FJB+GzwrX20OwmgRsY8bKB8S1Kv6eIshK2d6m
zIsRp2pJP5O/BhkUWbpaQmOT+bw35d6RcHuzrFGah1IbzdwL57Rr5IG28tLSJYXOXKkQ+ZlLWKZS
0k8tm+CfJVrwRLmttjq4iVKOnGTNtaIMPA8nGeZOCqiXbUs1cGzOlOAWffpILGmhqwnCe/fgHjO4
zzlNrDZ+npjGy1HsdVPVknS365B1YQMSRam7kX4HuxzfewUnWHpMfrpZfRR8VivMQNCeBb0s/osS
1tM1ne3QeJa8Mb9npFz9ZdJaenzOzcFY8NV74A==</SignatureValue><KeyInfo><KeyName>FC0A17A7ABD72369726EA4D4DBEF9838128A7C78</KeyName></KeyInfo></Signature></DirectoryRes>', __DIR__ . '/../Resources/aquirer-certificate.cer'),
			);
	}

	public function testGetType()
	{
		$this->assertEquals('DirectoryRes', $this->response->getType());
	}

	public function testGetCreationDateTime()
	{
		$this->assertEquals('2013-04-28T19:50:23+0000', $this->response->getCreationDateTime()->format(\DateTime::ISO8601));
	}
}