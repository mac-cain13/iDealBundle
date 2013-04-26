<?php

namespace Wrep\IDealBundle\IDeal;

use Buzz\Browser;
use Buzz\Client\Curl;

class IDealClient
{
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
		$merchantId = (int)$merchantId;
		if (!is_int($merchantId) || $merchantId <= 0) {
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
		$xml = $this->signXml($xml);

		$this->post( $xml->asXml() );
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
		$xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><' . $rootElement . ' />');
		$xml->addAttribute('xmlns', 'http://www.idealdesk.com/ideal/messages/mer-acq/3.3.1');
		$xml->addAttribute('version', '3.3.1');
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
		$timestamp = $utcTime->format('Y-m-d\TH:i:s.000\Z');

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

	/**
	 * Sign the given XML element
	 *
	 * @param \SimpleXMLElement The XML element to sign, will not be modified
	 * @return \SimpleXMLElement A new XML element fully signed
	 */
	protected function signXml(\SimpleXMLElement $xml)
	{
		// Convert SimpleXMLElement to DOMElement for signing
		$doc = new \DOMDocument();
		$doc->loadXML( $xml->asXml() );

		// Decode the private key so we can use it to sign the request
		$privateKey = new \XMLSecurityKey(\XMLSecurityKey::RSA_SHA256, array('type' => 'private'));
		$privateKey->passphrase = $this->merchantCertificatePassphrase;
		$privateKey->loadKey($this->merchantCertificate, true);

		// Create and configure the DSig helper and calculate the signature
		$xmlDSigHelper = new \XMLSecurityDSig();
		$xmlDSigHelper->setCanonicalMethod(\XMLSecurityDSig::EXC_C14N);
		$xmlDSigHelper->addReference($doc, \XMLSecurityDSig::SHA256, array('http://www.w3.org/2000/09/xmldsig#enveloped-signature'), array('force_uri' => true));
		$xmlDSigHelper->sign($privateKey);

		// Append the signature to the XML and save it for modification
		$signature = $xmlDSigHelper->appendSignature($doc->documentElement);

		// Calculate the fingerprint of the certificate
		$thumbprint = \XMLSecurityKey::getRawThumbprint( file_get_contents($this->merchantCertificate) );

		// Append the KeyInfo and KeyName elements to the signature
		$keyInfo = $signature->ownerDocument->createElementNS(\XMLSecurityDSig::XMLDSIGNS, 'KeyInfo');
		$signature->appendChild($keyInfo);
		$keyName = $keyInfo->ownerDocument->createElementNS(\XMLSecurityDSig::XMLDSIGNS, 'KeyName', $thumbprint);
		$keyInfo->appendChild($keyName);

		// Convert back to SimpleXMLElement and return
		return new \SimpleXMLElement( $doc->saveXML() );
	}
}