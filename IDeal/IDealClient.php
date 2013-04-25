<?php

namespace Wrep\IDealBundle\IDeal;

use Buzz\Browser;
use Buzz\Client\Curl;

class IDealClient
{
	const XML_DECLARATION = '<?xml version="1.0" encoding="UTF-8"?>';
	const ROOT_XMLNS = 'http://www.idealdesk.com/ideal/messages/mer-acq/3.3.1';
	const ROOT_VERSION = '3.3.1';

	private $merchantId;
	private $merchantSubId;
	private $merchantCertificate;
	private $merchantCertificatePassphrase;
	private $acquirerUrl;
	private $acquirerCertificate;

	private $browser;

	/**
	 * Construct an IDealClient
	 *
	 * @param int Your merchant identifier
	 * @param int Your merchant sub-identifier, if you don't know this it's probably zero
	 * @param string Path to your merchant certificate (PEM file)
	 * @param string|null Optional passphrase for your merchant certificate
	 * @param string The acquirer URL, can be the URL of a testing environment
	 * @param string The acquirer certificate, used to verify if we're really connected to the correct acquirer
	 * @param int Optional timeout in seconds when connecting to the aquirer, default 15 seconds
	 */
	public function __construct($merchantId, $merchantSubId, $merchantCertificate, $merchantCertificatePassphrase, $acquirerUrl, $acquirerCertificate, $acquirerTimeout = 15)
	{
		// Validate the merchant ID, must be a 9 digit or less positive integer
		if (!is_int($merchantId) || $merchantId < 0) {
			throw new \RuntimeException('The merchant ID must a positive integer. (' . $merchantId . ')');
		} else if (strlen($merchantId) > 9) {
			throw new \RuntimeException('The merchant ID must be 9 digits or less. (' . $merchantId . ')');
		}

		// Validate the merchant sub-identifier
		if (!is_int($merchantSubId) || $merchantSubId < 0) {
			throw new \RuntimeException('The merchant subID must a positive integer. (' . $merchantSubId . ')');
		} else if (strlen($merchantSubId) > 6) {
			throw new \RuntimeException('The merchant subID must be 6 digits or less. (' . $merchantSubId . ')');
		}

		// Check if the merchant certificate exists
		if ( !is_file($merchantCertificate) ) {
			throw new \RuntimeException('The merchant certificate doesn\'t exists. (' . $merchantCertificate . ')');
		}

		// Check if the acquirer certificate exists
		if ( !is_file($acquirerCertificate) ) {
			throw new \RuntimeException('The acquirer certificate doesn\'t exists. (' . $acquirerCertificate . ')');
		}

		// Check if the timeout is >0
		if ((int)$acquirerTimeout < 1) {
			throw new \RuntimeException('The acquirer timout must be above zero. (' . $acquirerTimeout . ')');
		}

		// Save the parameters
		$this->merchantId = sprintf('%09d', $merchantId);
		$this->merchantSubId = (int)$merchantSubId;
		$this->merchantCertificate = $merchantCertificate;
		$this->merchantCertificatePassphrase = $merchantCertificatePassphrase;
		$this->acquirerUrl = rtrim($acquirerUrl, '/');
		$this->acquirerCertificate = $acquirerCertificate;

		// Create a Buzz client and browser
		$client = new Curl();
		$client->setTimeout($acquirerTimeout);
		$client->setVerifyPeer(true);

		$this->browser = new Browser($client);
	}

	/**
	 * Fetch the issuer list
	 *
	 * @return array Ordered list of IDealIssuers
	 */
	public function fetchIssuerList()
	{
		$xml = $this->createRequest('DirectoryReq');
		$this->addMerchant($xml);
		$this->signXml($xml);

		echo $xml->asXml();die();
	}

	public function doTransaction()
	{
		;
	}

	public function fetchStatus()
	{
		;
	}

	protected function post($content)
	{
		$headers = array(	'Content-Type' => 'text/xml; charset=”utf-8”',
							'Accept' => 'text/xml');

		$response = $this->browser->post($this->acquirerUrl, $headers, $content);
	}

	/**
	 * Adds the xmlns and version attributes to the element
	 *
	 * @param string The type of request to create
	 * @return \SimpleXMLElement The XML element
	 */
	protected function createRequest($rootElement)
	{
		$xml = new \SimpleXMLElement(self::XML_DECLARATION . '<' . $rootElement . ' />');
		$xml->addAttribute('xmlns', self::ROOT_XMLNS);
		$xml->addAttribute('version', self::ROOT_VERSION);
		$this->addCreateDateTimestamp($xml);

		return $xml;
	}

	/**
	 * Adds the createDateTimestamp element to the XML containing the current time as ISO8601 string with UTC timzone
	 *
	 * @param \SimpleXMLElement The XML element to append the child to
	 * @return \SimpleXMLElement The added createDateTimestamp element
	 */
	protected function addCreateDateTimestamp(\SimpleXMLElement $xml)
	{
		// Create the UTC ISO8601 timestamp
		$utcTime = new \DateTime('now');
		$utcTimezone = new \DateTimeZone('UTC');
		$utcTime->setTimezone($utcTimezone);
		$timestamp = $utcTime->format(\DateTime::ISO8601);

		// Append the child element
		return $xml->addChild('createDateTimestamp', $timestamp );
	}

	/**
	 * Adds the Merchant element, optionally containing the return URL
	 *
	 * @param \SimpleXMLElement The XML element to append the child to
	 * @param string|null Optional, the merchant return URL to add
	 * @return \SimpleXMLElement The added Merchant element
	 */
	protected function addMerchant(\SimpleXMLElement $xml, $merchantReturnURL = null)
	{
		$merchant = $xml->addChild('Merchant');
		$merchant->addChild('merchantID', $this->merchantId);
		$merchant->addChild('subID', $this->merchantSubId);

		if (null != $merchantReturnURL) {
			$merchant->addChild('merchantReturnURL', $merchantReturnURL);
		}

		return $merchant;
	}

	private function signXml(\SimpleXMLElement $xml)
	{
		$doc = new \DOMDocument();
		$doc->loadXML( $xml->asXml() );

		$objKey = new \XMLSecurityKey(\XMLSecurityKey::RSA_SHA256, array('type' => 'private'));
		$objKey->loadKey('/Users/mathijs/projecten/1blikagenda/web/src/Blik/AgendaBundle/Resources/ideal/priv_nopwd.key', true);

		$objXMLSecDSig = new \XMLSecurityDSig();
		$objXMLSecDSig->setCanonicalMethod(\XMLSecurityDSig::EXC_C14N);
		$objXMLSecDSig->addReference($doc, \XMLSecurityDSig::SHA256, array('http://www.w3.org/2000/09/xmldsig#enveloped-signature'), array('force_uri' => true));
		$objXMLSecDSig->sign($objKey, $doc->documentElement);

		/* Add associated public key */
		$objXMLSecDSig->add509Cert('/Users/mathijs/projecten/1blikagenda/web/src/Blik/AgendaBundle/Resources/ideal/cert.cer', true, true);

		echo $doc->saveXML(); die();
	}
}