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
		$this->acquirerUrl = $acquirerUrl;
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
		$message = new IDealMessage('DirectoryReq', $this->merchantCertificate, $this->merchantCertificatePassphrase);
		$message->addMerchant($this->merchantId, $this->merchantSubId);

		$this->sendMessage($message);
	}

	public function doTransaction()
	{
		;
	}

	public function fetchStatus()
	{
		;
	}

	protected function sendMessage(IDealMessage $message)
	{
		$response = $this->browser->post(	$this->acquirerUrl,
											array('Content-Type' => 'text/xml; charset=”utf-8”', 'Accept' => 'text/xml'),
											(string)$message);

		echo $content . "\n\n\n\n\n";
		print_r($response);
	}
}