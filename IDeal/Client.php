<?php

namespace Wrep\IDealBundle\IDeal;

use Buzz\Browser;
use Buzz\Client\Curl;
use Wrep\IDealBundle\Exception\IDealException;

class Client
{
	private $merchant;
	private $acquirer;
	private $browser;

	/**
	 * Construct an Client
	 *
	 * @param Merchant The merchant to represent
	 * @param Acquirer The acquirer to connect to
	 * @param int Optional timeout in seconds when connecting to the aquirer, default 15 seconds
	 *
	 * @throws \RuntimeException if a parameter is invalid
	 */
	public function __construct(Merchant $merchant, Acquirer $acquirer, $timeout = 15)
	{
		// Check if the timeout is at least 1 second
		$timeout = (int)$timeout;
		if ($timeout < 1) {
			throw new \RuntimeException('The connection timout must be at least 1 second. (' . $timeout . ')');
		}

		// Save the parameters
		$this->merchant = $merchant;
		$this->acquirer = $acquirer;

		// Create a Buzz client and browser
		$client = new Curl();
		$client->setTimeout($timeout);
		$client->setVerifyPeer(true);

		$this->browser = new Browser($client);
	}

	/**
	 * Fetch the issuer list
	 *
	 * @return array Ordered list of Issuer objects
	 */
	public function fetchIssuerList()
	{
		$request = new Request(Request::TYPE_DIRECTORY, $this->merchantCertificate, $this->merchantCertificatePassphrase);
		$request->addMerchant($this->merchantId, $this->merchantSubId);

		$response = $this->sendRequest($request);

		// TODO: IssuerResponse maken waar je overheen kunt loopen etc
		$issuers = array();
		foreach ($response->getXML()->Directory->Country as $country)
		{
			foreach ($country->Issuer as $issuer)
			{
				$issuers[] = new Issuer((string)$issuer->issuerID, (string)$issuer->issuerName, (string)$country->countryNames);
			}
		}

		return $issuers;
	}

	// TODO: IssuerID interface maken die parent is van Issuer zodat je 'm zelf makkelijk kan maken
	public function doTransaction(Transaction $transaction, Issuer $issuer, $returnUrl)
	{
		// TODO: Check of de transactie niet al gestart is

		$request = new Request(Request::TYPE_TRANSACTION, $this->merchantCertificate, $this->merchantCertificatePassphrase);
		$request->addIssuer($issuer);
		$request->addMerchant($this->merchantId, $this->merchantSubId, $returnUrl);
		$request->addTransaction($transaction);

		$response = $this->sendRequest($request);

		$transaction->setTransactionId((string)$response->getXml()->Transaction->transactionID);
		return (string)$response->getXml()->Issuer->issuerAuthenticationURL;
	}

	// TODO: TransactionID interface maken die parent is van Issuer zodat je 'm zelf makkelijk kan maken
	public function updateStatus(Transaction $transaction)
	{
		// TODO: Check of de transactie wel een ID heeft
		$request = new Request(Request::TYPE_STATUS, $this->merchantCertificate, $this->merchantCertificatePassphrase);
		$request->addMerchant($this->merchantId, $this->merchantSubId);
		$request->addTransaction($transaction);

		$response = $this->sendRequest($request);

		$transaction->setStatus( (string)$response->getXml()->Transaction->status );
		// TODO: Andere data ook in de transactie setten
	}

	/**
	 * Send a Request to the Acquirer, parse the reponse and return a Response
	 *
	 * @param Request the request to send
	 *
	 * @return Response the response
	 *
	 * @throws IDealException if something went wrong
	 */
	protected function sendRequest(Request $request)
	{
		$response = $this->browser->post(	$this->acquirerUrl,
											$request->getHeaders(),
											$request->getContent() );

		// Check if the request was rejected by the acquirer
		if ( !$response->isSuccessful() ) {
			throw new IDealException( 'The iDeal acquirer responded with HTTP statuscode #' . $response->getStatusCode() . ' - ' . $response->getReasonPhrase() );
		}

		// TODO: Check of 't een AcquirerErrorRes-response is, dan hebben we ook een error

		// Parse the content
		return new Response($response->getContent(), $this->acquirerCertificate);
	}
}