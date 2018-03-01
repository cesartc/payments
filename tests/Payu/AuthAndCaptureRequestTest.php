<?php
/**
 * Created by PhpStorm.
 * User: cesar
 * Date: 2/17/18
 * Time: 11:38 PM
 */

namespace Tests\Payu;

use App\Libraries\Payu\AuthAndCaptureRequest;
use App\Libraries\Payu\AuthAndCaptureResponse;
use App\Exceptions\Payu\PayuResponseException;
use GuzzleHttp\Client;

class AuthAndCaptureRequestTest extends \TestCase
{
    private $httpClient;

    public function setUp()
    {
        parent::setUp();
        $this->httpClient = new Client();
    }

    public function testRequestBodyHasTheRightKeys()
    {
        $authAndCaptureRequest = new AuthAndCaptureRequest($this->httpClient);
        $bodyArray = $authAndCaptureRequest->getBody();
        $keys = ['language', 'command', 'merchant', 'transaction', 'test'];

        foreach ($keys as $key) {
            $this->assertArrayHasKey($key, $bodyArray);
        }
    }

    public function testOnLoadBodyValues()
    {
        $authAndCaptureRequest = new AuthAndCaptureRequest($this->httpClient);
        $bodyArray = $authAndCaptureRequest->getBody();

        $this->assertEquals(
            env('PAYU_ACCOUNT_ID'),
            $bodyArray['transaction']['order']['accountId']
        );
        $this->assertEquals(
            env('PAYU_LANGUAGE'),
            $bodyArray['transaction']['order']['language']
        );
    }

    public function testSetBodyValues()
    {
        $request = new AuthAndCaptureRequest($this->httpClient);
        $orderId = 12345;
        $request->setOrderId($orderId);
        $longOrderDescription = str_repeat('x', 260);
        $request->setOrderDescription($longOrderDescription);

        $bodyArray = $request->getBody();

        $this->assertInternalType(
            \PHPUnit_Framework_Constraint_IsType::TYPE_STRING,
            $bodyArray['transaction']['order']['referenceCode']
        );
        $this->assertEquals(
            $bodyArray['transaction']['order']['referenceCode'],
            $orderId
        );

        $this->assertEquals(
            255,
            strlen($bodyArray['transaction']['order']['description'])
        );

        $amountA = 123.45899;
        $expectedAmountA = "123.45";
        $request->setAmount($amountA);
        $bodyArray = $request->getBody();
        $this->assertInternalType(
            \PHPUnit_Framework_Constraint_IsType::TYPE_STRING,
            $bodyArray['transaction']['order']['additionalValues']['TX_VALUE']['value']
        );
        $this->assertEquals(
            $expectedAmountA,
            $bodyArray['transaction']['order']['additionalValues']['TX_VALUE']['value']
        );

        $amountB = 123;
        $expectedAmountB = "123";
        $request->setAmount($amountB);
        $bodyArray = $request->getBody();
        $this->assertEquals(
            $expectedAmountB,
            $bodyArray['transaction']['order']['additionalValues']['TX_VALUE']['value']
        );

        $amountC = 123.7;
        $expectedAmountC = "123.7";
        $request->setAmount($amountC);
        $bodyArray = $request->getBody();
        $this->assertEquals(
            $expectedAmountC,
            $bodyArray['transaction']['order']['additionalValues']['TX_VALUE']['value']
        );
    }

    public function testSignatureIsSetWhenItIsRequired()
    {
        $request = new AuthAndCaptureRequest($this->httpClient);
        $request->setOrderId(123);
        $request->setAmount(32.10);

        $methodName = 'consolidateRequestBody';

        $consolidateRequestBodyMethod = self::getMethod(
            $methodName,
            AuthAndCaptureRequest::class
        );

        $consolidateRequestBodyMethod->invoke($request);

        $body = $request->getBody();

        $this->assertTrue(strlen($body['transaction']['order']['signature']) > 0);
    }

    public function testParseResponseWhenSuccessfulTransaction()
    {
        $responseArray = [
            "code" => "SUCCESS",
            "error" => null,
            "transactionResponse" => [
                "orderId" => 843798727,
                "transactionId" => "d050c96b-d113-4754-a92e-a68487f7010e",
                "state" => "PENDING",
                "paymentNetworkResponseCode" => null,
                "paymentNetworkResponseErrorMessage" => null,
                "authorizationCode" => null,
                "pendingReason" => "PENDING_REVIEW",
                "responseCode" => "PENDING_TRANSACTION_REVIEW",
                "errorCode" => null,
                "responseMessage" => null,
                "transactionDate" => null,
                "transactionTime" => null,
                "operationDate" => null,
                "referenceQuestionnaire" => null,
                "extraParameters" => null,
                "additionalInfo" => null
            ]
        ];

        $tx = $responseArray['transactionResponse'];

        $request = new AuthAndCaptureRequest($this->httpClient);
        $response = $request->parseResponse($responseArray);

        $this->assertInstanceOf(
            AuthAndCaptureResponse::class,
            $response,
            'Response should be of the type ' . AuthAndCaptureResponse::class
        );

        $this->assertEquals($responseArray['code'], $response->getCode());

        $this->assertTrue($response->isSuccess());

        $this->assertEquals($tx['orderId'], $response->getOrderId());

        $this->assertEquals($tx['transactionId'], $response->getTransactionId());

        $this->assertEquals($tx['state'], $response->getTransactionState());

        $this->assertEquals($tx['responseCode'], $response->getResponseCode());

        $this->assertEquals($tx['responseMessage'], $response->getResponseMessage());
    }

    public function testExceptionIsThrownWhenCodeIsError()
    {
        $responseArray = [
            "code" => "ERROR",
            "error" => "Invalid request format",
            "transactionResponse" => null
        ];
        $request = new AuthAndCaptureRequest($this->httpClient);

        $this->setExpectedException(PayuResponseException::class);

        $request->parseResponse($responseArray);
    }
}
