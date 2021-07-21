<?php

namespace Ebizmarts\SagePaySuite\Test\Api;

use Ebizmarts\SagePaySuite\Model\Config;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

class PiTransactionTest extends WebapiAbstract
{
    const TEST_API_KEY = "snEEZ7EFaM5q9GzBspep";
    const TEST_API_PASSWORD = "MrzrB8u3CST4FLLNRXL6";
    const VALID_MERCHANT_SESSION_KEY = "/^[A-F0-9]{8}-[A-F0-9]{4}-[A-F0-9]{4}-[A-F0-9]{4}-[A-F0-9]{12}$/";
    const TEST_CC_NUMBER = "4929000005559";
    const TEST_CC_CV2 = "123";
    const TEST_CC_TYPE = "VI";
    const TEST_CC_EXPIRY = "0321";

    /** @var \Magento\Framework\ObjectManagerInterface */
    private $objectManager;

    /** @var \Magento\Config\Model\ResourceModel\Config */
    private $config;

    /** @var  \Ebizmarts\SagePaySuite\Test\Api\Helper */
    private $helper;

    /** @var \Magento\Framework\HTTP\Adapter\Curl */
    private $curl;

    /** @var \Ebizmarts\SagePaySuite\Model\Api\PIRest */
    private $piRestApi;

    protected function setUp()
    {
        $this->config = Bootstrap::getObjectManager()->create(
            \Magento\Config\Model\ResourceModel\Config::class
        );

        $this->objectManager = Bootstrap::getObjectManager();

        $this->helper = $this->objectManager->create("Ebizmarts\SagePaySuite\Test\Api\Helper");
        $this->curl = $this->objectManager->create("Magento\Framework\HTTP\Adapter\Curl");
        $this->piRestApi = $this->objectManager->create("Ebizmarts\SagePaySuite\Model\Api\PIRest");
    }

    /**
     * @magentoApiDataFixture Ebizmarts/SagePaySuite/_files/quote_with_sagepaysuitepi_payment.php
     */
    public function testPiCompleteTransaction()
    {
        $this->config->saveConfig("sagepaysuite/global/currency", Config::CURRENCY_BASE, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
        $this->config->saveConfig("currency/options/base", "USD", ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
        $this->config->saveConfig("currency/options/default", "USD", ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
        $this->config->saveConfig("currency/options/allow", "USD", ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
        $this->config->saveConfig("sagepaysuite/global/mode", Config::MODE_DEVELOPMENT);

        $this->helper->savePiKey();
        $this->helper->savePiPassword();

        $merchantSessionKey = $this->obtainMerchantSessionKey();
        $cardIdentifier = $this->getCardIdentifier($merchantSessionKey);

        $response = $this->payAndCreateOrder($cardIdentifier, $merchantSessionKey);

        $transactionDetails = $this->piRestApi->transactionDetails($response['transaction_id']);

        $this->assertEquals("USD", $transactionDetails->getCurrency());
    }

    /**
     * @magentoApiDataFixture Ebizmarts/SagePaySuite/_files/quote_with_sagepaysuitepi_payment.php
     */
    public function testPiCompleteTransactionCurrencyOptions()
    {
        $this->config->saveConfig("sagepaysuite/global/currency", Config::CURRENCY_BASE, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
        $this->config->saveConfig("currency/options/base", "GBP", ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
        $this->config->saveConfig("currency/options/default", "USD", ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
        $this->config->saveConfig("currency/options/allow", "GBP,EUR,USD", ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);

        $this->helper->savePiKey();
        $this->helper->savePiPassword();

        $merchantSessionKey = $this->obtainMerchantSessionKey();

        $cardIdentifier = $this->getCardIdentifier($merchantSessionKey);

        $response = $this->payAndCreateOrder($cardIdentifier, $merchantSessionKey);

        $transactionDetails = $this->piRestApi->transactionDetails($response['transaction_id']);

        $this->assertEquals("GBP", $transactionDetails->getCurrency());
    }

    /**
     * @param $merchantSessionKey
     * @return mixed
     */
    private function getCardIdentifier($merchantSessionKey)
    {
        $payload = [
            "cardDetails" => [
                "cardholderName" => "Owner",
                "cardNumber" => self::TEST_CC_NUMBER,
                "expiryDate" => self::TEST_CC_EXPIRY,
                "securityCode" => self::TEST_CC_CV2,
            ]
        ];

        $this->curl->write(
            \Zend_Http_Client::POST,
            "http://pi-test.sagepay.com/api/v1/card-identifiers", //http because of proxy
            '1.0',
            ["Content-type: application/json", "Authorization: Bearer $merchantSessionKey", "Cache-Control: no-cache"],
            json_encode($payload)
        );

        $cardIdentifierResponseBody = $this->curl->read();
        $cardIdentifierResponse = \Zend_Http_Response::extractBody($cardIdentifierResponseBody);

        $this->assertJson($cardIdentifierResponse);

        $cardIdentifierResponseObject = json_decode($cardIdentifierResponse);
        $this->assertObjectHasAttribute("cardIdentifier", $cardIdentifierResponseObject);

        return $cardIdentifierResponseObject->cardIdentifier;
    }

    /**
     * @return mixed
     */
    private function obtainMerchantSessionKey()
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/sagepay/pi-msk',
                'httpMethod'   => Request::HTTP_METHOD_GET,
            ],
        ];
        $response    = $this->_webApiCall($serviceInfo, []);

        return $response['response'];
    }

    /**
     * @return mixed
     */
    private function getCartId()
    {
        $quote = $this->objectManager->create('Magento\Quote\Model\Quote')->load('test_order_1', 'reserved_order_id');
        return $quote->getId();
    }

    /**
     * @param $cardIdentifier
     * @param $merchantSessionKey
     * @return array|bool|float|int|string
     */
    private function payAndCreateOrder($cardIdentifier, $merchantSessionKey)
    {
        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/sagepay/pi',
                'httpMethod'   => Request::HTTP_METHOD_POST,
            ]
        ];
        $body        = [
            "cartId"      => $this->getCartId(),
            "requestData" => [
                "card_identifier"      => $cardIdentifier,
                "cc_exp_month"         => substr(self::TEST_CC_EXPIRY, 0, 2),
                "cc_exp_year"          => substr(self::TEST_CC_EXPIRY, -2),
                "cc_last_four"         => substr(self::TEST_CC_NUMBER, -4),
                "cc_type"              => self::TEST_CC_TYPE,
                "merchant_session_key" => $merchantSessionKey,
                "javascript_enabled"   => 1,
                'accept_headers' => 'Accept headers.',
                'language' => "en-US",
                'user_agent' => "morcilla firefox",
                'java_enabled' => 1,
                'color_depth' => 32,
                'screen_width' => 1024,
                'screen_height' => 768,
                'timezone' => 180
            ]
        ];

        $response = $this->_webApiCall($serviceInfo, $body);

        $this->assertEquals("Ok", $response["status"]);
        $this->arrayHasKey("transaction_id");
        $this->assertEmpty($response["error_message"]);

        return $response;
    }
}
