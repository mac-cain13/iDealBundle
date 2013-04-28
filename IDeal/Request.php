<?php

namespace Wrep\IDealBundle\IDeal;

class Request
{
	const TYPE_DIRECTORY = 'DirectoryReq';
	const TYPE_TRANSACTION = 'AcquirerTrxReq';
	const TYPE_STATUS = 'AcquirerStatusReq';

	private $xml;
	private $merchantCertificate;
	private $merchantCertificatePassphrase;

	/**
	 * Construct an Request
	 *
	 * @param string The type of request to create, for example DirectoryReq
	 * @param string Path to your merchant certificate (PEM file)
	 * @param string|null Optional passphrase for your merchant certificate
	 *
	 * @throws \RuntimeException if a parameter is invalid
	 */
	public function __construct($requestType, $merchantCertificate, $merchantCertificatePassphrase = null)
	{
		if ($requestType == null) {
			throw new \RuntimeException('No request type given.');
		} else if ( !ctype_alnum($requestType) ) {
			throw new \RuntimeException('Request type must be alphanumeric. (' . $requestType . ')');
		}

		// Check if the merchant certificate exists
		if ( !is_file($merchantCertificate) ) {
			throw new \RuntimeException('The merchant certificate doesn\'t exists. (' . $merchantCertificate . ')');
		}

		// Create the basic XML structure
		$this->xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><' . $requestType . ' />');
		$this->xml->addAttribute('xmlns', 'http://www.idealdesk.com/ideal/messages/mer-acq/3.3.1');
		$this->xml->addAttribute('version', '3.3.1');
		$this->addCreateDateTimestamp();

		// Save the certificate info
		$this->merchantCertificate = $merchantCertificate;
		$this->merchantCertificatePassphrase = $merchantCertificatePassphrase;
	}

	/**
	 * Adds the createDateTimestamp element to the XML containing the current time as ISO8601 string in UTC timezone
	 *
	 * @return \SimpleXMLElement The added createDateTimestamp element
	 */
	private function addCreateDateTimestamp()
	{
		// Create the UTC ISO8601 timestamp (iDeal style)
		$utcTime = new \DateTime('now');
		$utcTimezone = new \DateTimeZone('UTC');
		$utcTime->setTimezone($utcTimezone);
		$timestamp = $utcTime->format('Y-m-d\TH:i:s.000\Z');

		return $this->xml->addChild('createDateTimestamp', $timestamp);
	}

	/**
	 * Adds the Merchant element, optionally containing the return URL
	 *
	 * @param int Merchant ID to add
	 * @param int Optional, Merchant subID to add
	 * @param string|null Optional, the merchant return URL to add
	 *
	 * @return \SimpleXMLElement The added Merchant element
	 *
	 * @throws \RuntimeException if a parameter is invalid
	 */
	public function addMerchant($merchantId, $merchantSubId = 0, $merchantReturnURL = null)
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

		// Validate the merchant return URL
		if ( strlen((string)$merchantReturnURL) > 512 ) {
			throw new \RuntimeException('The merchant return URL must be a string of 512 characters or less. (' . (string)$merchantReturnURL . ')');
		}

		$merchant = $this->xml->addChild('Merchant');
		$merchant->addChild('merchantID', sprintf('%09d', $merchantId) );
		$merchant->addChild('subID', $merchantSubId);

		if (null != $merchantReturnURL) {
			$merchant->addChild('merchantReturnURL', $merchantReturnURL);
		}

		return $merchant;
	}

	/**
	 * Get the signed XML for this message
	 *
	 * @return \SimpleXMLElement A signed version of this message
	 */
	protected function getSignedXmlElement()
	{
		// Convert SimpleXMLElement to DOMElement for signing
		$xml = new \DOMDocument();
		$xml->loadXML( $this->xml->asXml() );

		// Decode the private key so we can use it to sign the request
		$privateKey = new \XMLSecurityKey(\XMLSecurityKey::RSA_SHA256, array('type' => 'private'));
		$privateKey->passphrase = $this->merchantCertificatePassphrase;
		$privateKey->loadKey($this->merchantCertificate, true);

		// Create and configure the DSig helper and calculate the signature
		$xmlDSigHelper = new \XMLSecurityDSig();
		$xmlDSigHelper->setCanonicalMethod(\XMLSecurityDSig::EXC_C14N);
		$xmlDSigHelper->addReference($xml, \XMLSecurityDSig::SHA256, array('http://www.w3.org/2000/09/xmldsig#enveloped-signature'), array('force_uri' => true));
		$xmlDSigHelper->sign($privateKey);

		// Append the signature to the XML and save it for modification
		$signature = $xmlDSigHelper->appendSignature($xml->documentElement);

		// Calculate the fingerprint of the certificate
		$thumbprint = \XMLSecurityKey::getRawThumbprint( file_get_contents($this->merchantCertificate) );

		// Append the KeyInfo and KeyName elements to the signature
		$keyInfo = $signature->ownerDocument->createElementNS(\XMLSecurityDSig::XMLDSIGNS, 'KeyInfo');
		$keyName = $keyInfo->ownerDocument->createElementNS(\XMLSecurityDSig::XMLDSIGNS, 'KeyName', $thumbprint);
		$keyInfo->appendChild($keyName);
		$signature->appendChild($keyInfo);

		// Convert back to SimpleXMLElement and return
		return new \SimpleXMLElement( $xml->saveXML() );
	}

	/**
	 * The fully signed message as XML string
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->getSignedXmlElement()->asXml();
	}
}