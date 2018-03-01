<?php
/**
 * Created by PhpStorm.
 * User: cesar
 * Date: 2/17/18
 * Time: 11:36 PM
 */

namespace App\Libraries\Payu;

abstract class PayuRequest
{
    /**
     * Client library which actually performs the requests
     *
     * @var \GuzzleHttp\Client
     */
    private $httpClient;

    /**
     * The body of the Payu request. This section is sent in all requests.
     *
     * @var array
     */
    private $baseBody;

    /**
     * The especific body for each different request (command)
     *
     * @var array
     */
    protected $extraBody = [];

    /**
     * Defines the request to perform, since Payu API does not use URIs for that
     *
     * @var string
     */
    protected $command;

    /**
     * The HTTP method of the request (POST, GET, PUT, DELETE, etc)
     *
     * @var string
     */
    protected $httpMethod;

    /**
     * List of request commands that require signature
     *
     * @var array
     */
    private $commandsThatRequireSignature = ['SUBMIT_TRANSACTION'];

    /**
     * PayuRequest constructor.
     *
     * @param \GuzzleHttp\Client $httpClient
     */
    public function __construct(\GuzzleHttp\Client $httpClient)
    {
        $this->httpClient = $httpClient;

        $this->baseBody = [
            'language' => env('PAYU_LANGUAGE'),
            'merchant' => [
                'apiKey' => env('PAYU_API_KEY'),
                'apiLogin' => env('PAYU_API_LOGIN')
            ],
            'test' => env('PAYU_TEST')
        ];

        $this->setUp();
    }

    /**
     * Executes the request
     *
     * @return PayuResponse
     */
    public function execute()
    {
        $this->consolidateRequestBody();
        $requestBody = $this->getBody();
        $response = $this->httpClient->request(
            $this->httpMethod,
            '',
            ['json' => $requestBody]
        );
        $body = json_decode($response->getBody(), true);

        return $this->parseResponse($body);
    }

    /**
     * Returns the complete body of the request
     *
     * @return array
     */
    public function getBody()
    {
        if (isset($this->command)) {
            $this->baseBody['command'] = $this->command;
        }
        return array_merge($this->baseBody, $this->extraBody);
    }

    /**
     * Generates the signature as Payu specifies
     * http://developers.payulatam.com/es/api/considerations.html
     *
     * @return string
     */
    protected function getSignature()
    {
        $toConvert = $this->baseBody['merchant']['apiKey'] . '~' .
            env('PAYU_MERCHANT_ID') . '~' .
            $this->extraBody['transaction']['order']['referenceCode'] . '~' .
            $this->extraBody['transaction']['order']['additionalValues']['TX_VALUE']['value'] . '~' .
            env('PAYU_CURRENCY');
        return md5(
            $toConvert
        );
    }

    /**
     * Sets the signature value if needed
     */
    protected function addSignature()
    {
        if (in_array($this->command, $this->commandsThatRequireSignature)) {
            $this->extraBody['transaction']['order']['signature'] = $this->getSignature();
        }
    }

    /**
     * Verifies all the required parameters before sending the request
     */
    protected function consolidateRequestBody()
    {
        $this->addSignature();
    }

    /**
     * Parses the body response
     *
     * @return PayuResponse
     */
    abstract protected function parseResponse(array $response);

    /**
     * Sets the required parameters on start up
     *
     * @return void
     */
    abstract protected function setUp();
}
