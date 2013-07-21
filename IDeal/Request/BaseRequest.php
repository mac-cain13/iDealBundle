<?php

namespace Wrep\IDealBundle\IDeal\Request;

use Wrep\IDealBundle\IDeal\Merchant;
use Wrep\IDealBundle\IDeal\IssuerId;

abstract class BaseRequest
{
	const TYPE_DIRECTORY = 'DirectoryReq';
	const TYPE_TRANSACTION = 'AcquirerTrxReq';
	const TYPE_STATUS = 'AcquirerStatusReq';

	private $issuer;
	private $merchant;
	private $transaction;
	private $returnUrl;

	/**
	 * Construct an Request
	 *
	 * @param string The type of request to create, for example DirectoryReq
	 * @param Merchant The merchant issuing this request
	 *
	 * @throws \RuntimeException if a parameter is invalid
	 */
	public function __construct($requestType, Merchant $merchant)
	{
		if ($requestType == null) {
			throw new \RuntimeException('No request type given.');
		} else if ( !ctype_alnum($requestType) ) {
			throw new \RuntimeException('Request type must be alphanumeric. (' . $requestType . ')');
		}

		// Create the basic XML structure
		$this->xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><' . $requestType . ' />');
		$this->xml->addAttribute('xmlns', 'http://www.idealdesk.com/ideal/messages/mer-acq/3.3.1');
		$this->xml->addAttribute('version', '3.3.1');

		// Add the creation timestamp
		$this->addCreateDateTimestamp();

		// Add the merchant
		$this->setMerchant($merchant);
	}

	protected function setIssuer(IssuerId $issuer)
	{
		$this->issuer = $issuer;
	}

	protected function setMerchant(Merchant $merchant)
	{
		if (null == $merchant) {
			throw new \RuntimeException('Merchant cannot be null.');
		}

		$this->merchant = $merchant;
	}

	protected function setTransaction(Transaction $transaction)
	{
		$this->transaction = $transaction;
	}

	protected function setReturnUrl($returnUrl)
	{
		// Validate the merchant return URL
		if ( strlen((string)$returnURL) > 512 ) {
			throw new \RuntimeException('The merchant return URL must be a string of 512 characters or less. (' . (string)$returnURL . ')');
		}

		$this->returnUrl = $returnUrl;
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
	public function addMerchantElement(\SimpleXMLElement $xml)
	{
		$merchant = $this->xml->addChild('Merchant');
		$merchant->addChild('merchantID', sprintf('%09d', $merchantId) );
		$merchant->addChild('subID', $merchantSubId);

		if (null != $returnURL) {
			$merchant->addChild('merchantReturnURL', $returnURL);
		}

		return $merchant;
	}

	public function addIssuer(Issuer $issuer)
	{
		$issuerXml = $this->xml->addChild('Issuer');
		$issuerXml->addChild('issuerID', $issuer->getId() );

		return $issuerXml;
	}

	public function addTransaction(Transaction $transaction)
	{
		$transactionXml = $this->xml->addChild('Transaction');

		// This is ugly! Should be some sort of Request builder to help with this in a nice way
		if ($transaction->getTransactionId() == null)
		{
			$transactionXml->addChild('purchaseID', $transaction->getPurchaseId() );
			$transactionXml->addChild('amount', $transaction->getAmount() . '0' );
			$transactionXml->addChild('currency', $transaction->getCurrency() );
			$transactionXml->addChild('expirationPeriod', 'PT15M' ); // TODO
			$transactionXml->addChild('language', $transaction->getLanguage() );
			$transactionXml->addChild('description', $transaction->getDescription() );
			$transactionXml->addChild('entranceCode', $transaction->getEntranceCode() );
		}
		else
		{
			$transactionXml->addChild('transactionID', $transaction->getTransactionId() );
		}

		return $transactionXml;
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

	public function getHeaders()
	{
		return array(	'Content-Type'	=> 'text/xml; charset="utf-8"',
						'Accept'		=> 'text/xml');
	}

	/**
	 * The fully signed message as XML string
	 *
	 * @return string
	 */
	public function getContent()
	{
		return $this->getSignedXmlElement()->asXml();
	}
}