<?php

namespace Wrep\IDealBundle\IDeal;

class IDealMessage
{
	private $xml;
	private $merchantCertificate;
	private $merchantCertificatePassphrase;

	/**
	 * Adds the xmlns and version attributes to the element
	 *
	 * @param string The type of request to create
	 * @param string Path to your merchant certificate (PEM file)
	 * @param string|null Optional passphrase for your merchant certificate
	 * @return \SimpleXMLElement The XML element
	 */
	public function __construct($rootElement, $merchantCertificate, $merchantCertificatePassphrase = null)
	{
		$this->xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><' . $rootElement . ' />');
		$this->xml->addAttribute('xmlns', 'http://www.idealdesk.com/ideal/messages/mer-acq/3.3.1');
		$this->xml->addAttribute('version', '3.3.1');
		$this->addCreateDateTimestamp();

		$this->merchantCertificate = $merchantCertificate;
		$this->merchantCertificatePassphrase = $merchantCertificatePassphrase;
	}

	/**
	 * Adds the createDateTimestamp element to the XML containing the current time as ISO8601 string with UTC timzone
	 *
	 * @return \SimpleXMLElement The added createDateTimestamp element
	 */
	private function addCreateDateTimestamp()
	{
		// Create the UTC ISO8601 timestamp
		$utcTime = new \DateTime('now');
		$utcTimezone = new \DateTimeZone('UTC');
		$utcTime->setTimezone($utcTimezone);
		$timestamp = $utcTime->format('Y-m-d\TH:i:s.000\Z');

		// Append the child element
		return $this->xml->addChild('createDateTimestamp', $timestamp);
	}

	/**
	 * Adds the Merchant element, optionally containing the return URL
	 *
	 * @param int Merchant ID to add
	 * @param int Optional, Merchant subID to add
	 * @param string|null Optional, the merchant return URL to add
	 * @return \SimpleXMLElement The added Merchant element
	 */
	public function addMerchant($merchantId, $merchantSubId = 0, $merchantReturnURL = null)
	{
		$merchant = $this->xml->addChild('Merchant');
		$merchant->addChild('merchantID', $merchantId);
		$merchant->addChild('subID', $merchantSubId);

		if (null != $merchantReturnURL) {
			$merchant->addChild('merchantReturnURL', $merchantReturnURL);
		}

		return $merchant;
	}

	/**
	 * Sign the message
	 *
	 * @param string Path to your merchant certificate (PEM file)
	 * @param string|null Optional passphrase for your merchant certificate
	 * @return \SimpleXMLElement A signed version of this message
	 */
	protected function getSignedXmlElement($merchantCertificate, $merchantCertificatePassphrase = null)
	{
		// Convert SimpleXMLElement to DOMElement for signing
		$xml = new \DOMDocument();
		$xml->loadXML( $this->xml->asXml() );

		// Decode the private key so we can use it to sign the request
		$privateKey = new \XMLSecurityKey(\XMLSecurityKey::RSA_SHA256, array('type' => 'private'));
		$privateKey->passphrase = $merchantCertificatePassphrase;
		$privateKey->loadKey($merchantCertificate, true);

		// Create and configure the DSig helper and calculate the signature
		$xmlDSigHelper = new \XMLSecurityDSig();
		$xmlDSigHelper->setCanonicalMethod(\XMLSecurityDSig::EXC_C14N);
		$xmlDSigHelper->addReference($xml, \XMLSecurityDSig::SHA256, array('http://www.w3.org/2000/09/xmldsig#enveloped-signature'), array('force_uri' => true));
		$xmlDSigHelper->sign($privateKey);

		// Append the signature to the XML and save it for modification
		$signature = $xmlDSigHelper->appendSignature($xml->documentElement);

		// Calculate the fingerprint of the certificate
		$thumbprint = \XMLSecurityKey::getRawThumbprint( file_get_contents($merchantCertificate) );

		// Append the KeyInfo and KeyName elements to the signature
		$keyInfo = $signature->ownerDocument->createElementNS(\XMLSecurityDSig::XMLDSIGNS, 'KeyInfo');
		$keyName = $keyInfo->ownerDocument->createElementNS(\XMLSecurityDSig::XMLDSIGNS, 'KeyName', $thumbprint);
		$keyInfo->appendChild($keyName);
		$signature->appendChild($keyInfo);

		// Convert back to SimpleXMLElement and return
		return new \SimpleXMLElement( $xml->saveXML() );
	}

	/**
	 * The message fully signed as XML
	 *
	 * @return string
	 */
	public function __toString()
	{
		return $this->getSignedXmlElement($this->merchantCertificate, $this->merchantCertificatePassphrase)->asXml();
	}
}