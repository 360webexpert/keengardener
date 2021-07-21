<?php
/**
 * Created by PhpStorm.
 * User: pablo
 * Date: 7/28/17
 * Time: 11:43 AM
 */

namespace Ebizmarts\SagePaySuite\Test\Api;

use Magento\Framework\App\Config;
use Magento\Framework\Webapi\Rest\Request;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\TestFramework\TestCase\WebapiAbstract;

class ServerRegisterTransactionTest extends WebapiAbstract
{
    /**
     * @var \Magento\TestFramework\ObjectManager
     */
    private $objectManager;

    /** @var \Magento\Config\Model\Config */
    private $config;

    /** @var  \Ebizmarts\SagePaySuite\Test\Api\Helper */
    private $helper;

    /** @var \Magento\Framework\HTTP\Adapter\Curl */
    private $curl;

    protected function setUp()
    {
        $this->objectManager = Bootstrap::getObjectManager();

        $this->helper = $this->objectManager->create("Ebizmarts\SagePaySuite\Test\Api\Helper");

        $this->config = $this->objectManager->create('Magento\Config\Model\Config');

        $this->curl = $this->objectManager->create("Magento\Framework\HTTP\Adapter\Curl");

        $this->helper->saveReportingApiUser();
        $this->helper->saveReportingApiPassword();

        $appConfig = $this->objectManager->get(Config::class);
        $appConfig->clean();
    }

    /**
     * @magentoApiDataFixture Ebizmarts/SagePaySuite/_files/quote_with_sagepaysuiteserver_payment.php
     */
    public function testRegisterServerTransacionAsGuest()
    {
        $this->config->setDataByPath("sagepaysuite/global/mode", \Ebizmarts\SagePaySuite\Model\Config::MODE_DEVELOPMENT);
        $this->config->save();
        $this->setPaymentActionAsPayment();

        $serviceInfo = [
            'rest' => [
                'resourcePath' => '/V1/sagepay-guest/server',
                'httpMethod' => Request::HTTP_METHOD_POST,
            ]
        ];

        $quote = $this->objectManager->create('Magento\Quote\Model\Quote')->load('test_order_1', 'reserved_order_id');

        /** @var \Magento\Checkout\Model\Session $checkoutSession */
        $checkoutSession = $this->objectManager->create('Magento\Checkout\Model\Session');

        $customerRepository = $this->objectManager->create('Magento\Customer\Api\CustomerRepositoryInterface');
        $customer = $customerRepository->getById(1);
        $checkoutSession->setCustomerData($customer);
        $checkoutSession->setQuoteId($quote->getId());
        $checkoutSession->getQuote();

        $cartId = $quote->getId();

        $quoteIdMask = $this->quoteMaskIdFromCartId($cartId);

        $response = $this->_webApiCall($serviceInfo, [
            'cartId'     => $quoteIdMask->getMaskedId(),
            'save_token' => false,
            'token'      => "%token%",
        ]);

        $this->checkThereIsNoErrorMessage($response);

        $this->checkResponseIsSuccess($response);

        $this->checkResponseHasRequiredData($response);

        $this->checkResponseCodeIsOk($response);

        $this->checkSagePayResponseDataIsCorrect($response);

        $this->checkOrderStatusIsPendingPayment();
    }

    private function setPaymentActionAsPayment()
    {
        $this->config->setDataByPath("payment/sagepaysuiteserver/payment_action", "PAYMENT");
        $this->config->save();
    }

    /**
     * @param $response
     */
    private function checkThereIsNoErrorMessage($response)
    {
        $this->assertEmpty($response["error_message"], $response["error_message"]);
    }

    /**
     * @param $response
     */
    private function checkResponseIsSuccess($response)
    {
        $this->assertTrue($response["success"]);
    }

    /**
     * @param $response
     */
    private function checkResponseHasRequiredData($response)
    {
        $this->assertCount(2, $response["response"]);
    }

    /**
     * @param $response
     */
    private function checkResponseCodeIsOk($response)
    {
        $this->assertEquals(200, $response["response"][0]);
    }

    /**
     * @param $response
     */
    private function checkSagePayResponseDataIsCorrect($response)
    {
        $sagePayResponseData = $response["response"][1];
        $sagePayResponseData = json_decode($sagePayResponseData, true);
        $this->assertEquals("3.00", $sagePayResponseData["VPSProtocol"]);
        $this->assertEquals("OK", $sagePayResponseData["Status"]);
        $this->assertEquals(
            "2014 : The Transaction was Registered Successfully.",
            $sagePayResponseData["StatusDetail"]
        );
        $this->assertArrayHasKey("VPSTxId", $sagePayResponseData);
        $this->assertArrayHasKey("SecurityKey", $sagePayResponseData);
        $this->assertArrayHasKey("NextURL", $sagePayResponseData);
        $this->assertCount(6, $sagePayResponseData);
    }

    private function checkOrderStatusIsPendingPayment()
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->objectManager->create('Magento\Sales\Model\Order')->load('test_order_1', 'increment_id');
        $this->assertEquals("pending_payment", $order->getStatus());
    }

    /**
     * @param $cartId
     * @return mixed
     */
    private function quoteMaskIdFromCartId($cartId)
    {
        $quoteIdMask = $this->objectManager
            ->create('Magento\Quote\Model\QuoteIdMaskFactory')->create()->load($cartId, 'quote_id');

        return $quoteIdMask;
    }
}
