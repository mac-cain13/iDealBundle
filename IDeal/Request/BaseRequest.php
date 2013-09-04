<?php

namespace Wrep\IDealBundle\IDeal\Request;

use Wrep\IDealBundle\IDeal\Merchant;
use Wrep\IDealBundle\IDeal\BIC;
use Wrep\IDealBundle\IDeal\Transaction;

use Wrep\IDealBundle\Exception\InvalidArgumentException;

abstract class BaseRequest
{
	const TYPE_DIRECTORY = 'DirectoryReq';
	const TYPE_TRANSACTION = 'AcquirerTrxReq';
	const TYPE_STATUS = 'AcquirerStatusReq';

	private $requestType;
	private $bic;
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
		$this->setRequestType($requestType);
		$this->setMerchant($merchant);
	}

	protected function setRequestType($requestType)
	{
		// Validate request type
		if ($requestType == null) {
			throw new InvalidArgumentException('No request type given.');
		} else if ( !ctype_alnum($requestType) ) {
			throw new InvalidArgumentException('Request type must be alphanumeric. (' . $requestType . ')');
		}

		$this->requestType = $requestType;
	}

	protected function setMerchant(Merchant $merchant)
	{
		if (null == $merchant) {
			throw new InvalidArgumentException('Merchant cannot be null.');
		}

		$this->merchant = $merchant;
	}

	protected function setBIC(BIC $bic)
	{
		$this->bic = $bic;
	}

	protected function setTransaction(Transaction $transaction)
	{
		$this->transaction = $transaction;
	}

	protected function setReturnUrl($returnUrl)
	{
		// Validate the merchant return URL
		if (!is_string($returnUrl) || strlen($returnURL) > 512) {
			throw new InvalidArgumentException('The merchant return URL must be a string of 512 characters or less. (' . $returnURL . ')');
		}

		$this->returnUrl = $returnUrl;
	}

	public function getHeaders()
	{
		return array('Content-Type'	=> 'text/xml; charset="utf-8"',
					 'Accept'		=> 'text/xml');
	}

	/**
	 * The fully signed message as XML string
	 *
	 * @return string
	 */
	public function getContent()
	{
		// Create the basic XML structure
		$xml = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><' . $this->requestType . ' />');
		$xml->addAttribute('xmlns', 'http://www.idealdesk.com/ideal/messages/mer-acq/3.3.1');
		$xml->addAttribute('version', '3.3.1');

		// Add the creation timestamp
		$this->addCreateDateTimestamp();

		// Add other elements
		if ($this->bic) {
			$this->addIssuerElement($xml);
		}

		if ($this->merchant) {
			$this->addMerchantElement($xml);
		}

		if ($this->transaction) {
			$this->addTransactionElement($xml);
		}

		return $this->signXml($xml)->asXML();
	}

	/**
	 * Adds the createDateTimestamp element to the XML containing the current time as ISO8601 string in UTC timezone
	 *
	 * @return \SimpleXMLElement The added createDateTimestamp element
	 */
	protected function addCreateDateTimestamp()
	{
		// Create the UTC ISO8601 timestamp (iDeal style)
		$utcTime = new \DateTime('now');
		$utcTimezone = new \DateTimeZone('UTC');
		$utcTime->setTimezone($utcTimezone);
		$timestamp = $utcTime->format('Y-m-d\TH:i:s.000\Z');

		return $this->xml->addChild('createDateTimestamp', $timestamp);
	}

	protected function addIssuerElement(\SimpleXMLElement $xml)
	{
		$issuerXml = $xml->addChild('Issuer');
		$issuerXml->addChild('issuerID', $this->bic->getCode() );

		return $issuerXml;
	}

	/**
	 * Adds the Merchant element, optionally containing the return URL
	 *
	 * @return \SimpleXMLElement The added Merchant element
	 */
	protected function addMerchantElement(\SimpleXMLElement $xml)
	{
		$merchantXml = $xml->addChild('Merchant');
		$merchantXml->addChild('merchantID', sprintf('%09d', $this->merchant->getId()) );
		$merchantXml->addChild('subID', $this->merchant->getSubId() );

		if (null != $returnURL) {
			$merchantXml->addChild('merchantReturnURL', $this->returnUrl);
		}

		return $merchantXml;
	}

	protected function addTransactionElement(\SimpleXMLElement $xml)
	{
		$transactionXml = $xml->addChild('Transaction');
		$transactionXml->addChild('purchaseID', $this->transaction->getPurchaseId() );
		$transactionXml->addChild('amount', $this->transaction->getAmount() );
		$transactionXml->addChild('currency', $this->transaction->getCurrency() );
		$transactionXml->addChild('expirationPeriod', $this->transaction->getExpirationPeriod()->format('P%yY%mM%dDT%hH%iM%sS') );
		$transactionXml->addChild('language', $this->transaction->getLanguage() );
		$transactionXml->addChild('description', $this->transaction->getDescription() );
		$transactionXml->addChild('entranceCode', $this->transaction->getEntranceCode() );

		return $transactionXml;
	}

	/**
	 * Get the signed XML for this message
	 *
	 * @return \SimpleXMLElement A signed version of this message
	 */
	protected function signXml()
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
}
