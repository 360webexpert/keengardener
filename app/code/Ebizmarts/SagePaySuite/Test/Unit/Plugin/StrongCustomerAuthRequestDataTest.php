<?php
namespace Ebizmarts\SagePaySuite\Test\Unit\Plugin;

use Ebizmarts\SagePaySuite\Model\Config;
use Ebizmarts\SagePaySuite\Model\PiRequest;
use Ebizmarts\SagePaySuite\Plugin\StrongCustomerAuthRequestData;
use Ebizmarts\SagePaySuite\Model\CryptAndCodeData;
use Magento\Framework\HTTP\PhpEnvironment\Request;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;
use Magento\Framework\UrlInterface;
use PHPUnit\Framework\TestCase;

class StrongCustomerAuthRequestDataTest extends TestCase
{
    const STRONG_CUSTOMER_AUTHENTICATION_KEY = 'strongCustomerAuthentication';
    const USER_AGENT = "Mozilla\/5.0 (Macintosh; Intel Mac OS X 10.14; rv:68.0) Gecko\/20100101 Firefox\/68.0";
    const BROWSER_LANGUAGE = "en-US";
    const NOTIFICATION_URL = "https://website.example/sagepaysuite/pi/callback3Dv2";
    const SERVICE_PURCHASE = "GoodsAndServicePurchase";
    const WINDOW_SIZE = "Large";
    const REMOTE_IP = "127.0.0.1";
    const ACCEPT_HEADER_ALL = "*\/*";
    const QUOTE_ID = 1;
    const ENCODED_QUOTE_ID = 'MDozOiswMXF3V0l1WFRLTDRra0wxUCtYSGgyQVdORUdWaXNPN3N5RUNEbzE,';

    private $objectManagerHelper;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }

    public function testNotScaTransactionConfig()
    {
        $configMock = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
        $configMock->expects($this->once())->method('shouldUse3dV2')->willReturn(false);
        $requestMock = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $urlMock = $this->getMockBuilder(UrlInterface::class)->disableOriginalConstructor()->getMock();
        $cryptAndCodeMock = $this->getMockBuilder(CryptAndCodeData::class)->disableOriginalConstructor()->getMock();

        $sut = $this->objectManagerHelper->getObject(
            StrongCustomerAuthRequestData::class,
            [
                'sagepayConfig' => $configMock,
                'request'       => $requestMock,
                'coreUrl'       => $urlMock,
                'cryptAndCode'  => $cryptAndCodeMock
            ]
        );

        $subjectMock = $this->getMockBuilder(PiRequest::class)->disableOriginalConstructor()->getMock();

        $result = $sut->afterGetRequestData($subjectMock, []);

        $this->assertEquals([], $result);
    }

    public function testScaTransaction()
    {
        $configMock = $this->getMockBuilder(Config::class)->disableOriginalConstructor()->getMock();
        $configMock->expects($this->once())->method('shouldUse3dV2')->willReturn(true);
        $configMock->expects($this->once())->method('getValue')->with("challengewindowsize")->willReturn(self::WINDOW_SIZE);

        $requestMock = $this->getMockBuilder(Request::class)->disableOriginalConstructor()->getMock();
        $requestMock->expects($this->once())->method('getHeader')->with('Accept')->willReturn(self::ACCEPT_HEADER_ALL);
        $requestMock->expects($this->once())->method('getClientIp')->willReturn(self::REMOTE_IP);

        $urlMock = $this->getMockBuilder(UrlInterface::class)->disableOriginalConstructor()->getMock();
        $urlMock->expects($this->once())->method('getUrl')->with("sagepaysuite/pi/callback3Dv2", ["_secure" => true, 'quoteId' => self::ENCODED_QUOTE_ID])
        ->willReturn(self::NOTIFICATION_URL);

        $cryptAndCodeMock = $this->getMockBuilder(CryptAndCodeData::class)->disableOriginalConstructor()->getMock();
        $cryptAndCodeMock->expects($this->once())->method('encryptAndEncode')->with(self::QUOTE_ID)->willReturn(self::ENCODED_QUOTE_ID);

        $cartMock = $this->getMockBuilder(\Magento\Quote\Model\Quote::class)->disableOriginalConstructor()->getMock();

        $sut = $this->objectManagerHelper->getObject(
            StrongCustomerAuthRequestData::class,
            [
                'sagepayConfig' => $configMock,
                'request'       => $requestMock,
                'coreUrl'       => $urlMock,
                'cryptAndCode'  => $cryptAndCodeMock
            ]
        );

        $piRequestMock = $this->getMockBuilder(\Ebizmarts\SagePaySuite\Api\Data\PiRequest::class)->disableOriginalConstructor()->getMock();
        $piRequestMock->expects($this->once())->method('getJavaEnabled')->willReturn(1);
        $piRequestMock->expects($this->once())->method('getColorDepth')->willReturn(24);
        $piRequestMock->expects($this->once())->method('getScreenHeight')->willReturn(1080);
        $piRequestMock->expects($this->once())->method('getScreenWidth')->willReturn(1920);
        $piRequestMock->expects($this->once())->method('getTimezone')->willReturn(180);
        $piRequestMock->expects($this->once())->method('getLanguage')->willReturn(self::BROWSER_LANGUAGE);
        $piRequestMock->expects($this->once())->method('getUserAgent')->willReturn(self::USER_AGENT);

        $subjectMock = $this->getMockBuilder(PiRequest::class)->disableOriginalConstructor()->getMock();
        $subjectMock->expects($this->once())->method('getRequest')->willReturn($piRequestMock);

        $subjectMock->expects($this->once())->method('getCart')->willReturn($cartMock);
        $cartMock->expects($this->once())->method('getId')->willReturn(self::QUOTE_ID);

        $result = $sut->afterGetRequestData($subjectMock, []);

        $this->assertArrayHasKey(self::STRONG_CUSTOMER_AUTHENTICATION_KEY, $result);
        $this->assertEquals ($this->getExpectedScaParameters(), $result[self::STRONG_CUSTOMER_AUTHENTICATION_KEY]);
    }

    /**
     * @return array
     */
    private function getExpectedScaParameters(): array
    {
        return [
            'browserJavascriptEnabled' => 1,
            'browserJavaEnabled'       => 1,
            'browserColorDepth'        => 24,
            'browserScreenHeight'      => 1080,
            'browserScreenWidth'       => 1920,
            'browserTZ'                => 180,
            'browserAcceptHeader'      => self::ACCEPT_HEADER_ALL,
            'browserIP'                => self::REMOTE_IP,
            'browserLanguage'          => self::BROWSER_LANGUAGE,
            'browserUserAgent'         => self::USER_AGENT,
            'notificationURL'          => self::NOTIFICATION_URL,
            'transType'                => self::SERVICE_PURCHASE,
            'challengeWindowSize'      => self::WINDOW_SIZE
        ];
    }
}