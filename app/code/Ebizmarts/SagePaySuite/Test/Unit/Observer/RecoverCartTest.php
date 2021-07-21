<?php

namespace Ebizmarts\SagePaySuite\Test\Unit\Observer;

use Ebizmarts\SagePaySuite\Observer\RecoverCart;
use Ebizmarts\SagePaySuite\Model\Session as SagePaySession;
use Magento\Framework\App\Request\Http;
use Magento\Framework\Event\Observer;
use Magento\Checkout\Model\Session;
use Magento\Framework\Message\ManagerInterface;
use Magento\Theme\Block\Html\Header\Logo;
use Magento\Framework\UrlInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class RecoverCartTest extends \PHPUnit\Framework\TestCase
{
    const TEST_BASE_URL = "http://magento.test/";
    const TEST_MESSAGE  = "There is an order in process. Click <a target='_self' href=http://magento.test/sagepaysuite/cart/recover>HERE</a> to recover the cart.";
    const TEST_ORDER_ID = 7832;
    const TEST_SUCCESS_ACTION_NAME = 'checkout_index_index';
    const TEST_SUCCESS_FRONT_NAME = 'checkout';
    const TEST_FAIL_ACTION_NAME = 'customer_section_load';
    const TEST_FAIL_FRONT_NAME = 'rest';

    /** @var Session */
    private $sessionMock;

    /** @var ManagerInterface */
    private $messageManagerMock;

    /** @var Logo */
    private $logoMock;

    /** @var UrlInterface */
    private $urlInterface;

    /** @var Observer */
    private $observerMock;

    /** @var RecoverCart */
    private $recoverCart;

    /** @var Http */
    private $requestMock;

    protected function setUp()
    {
        $this->sessionMock = $this
            ->getMockBuilder(Session::class)
            ->setMethods(['getData', 'setData'])
            ->disableOriginalConstructor()
            ->getMock();

        $this->messageManagerMock = $this
            ->getMockBuilder(ManagerInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->logoMock = $this
            ->getMockBuilder(Logo::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->urlInterface = $this
            ->getMockBuilder(UrlInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->observerMock = $this
            ->getMockBuilder(Observer::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->requestMock = $this
            ->getMockBuilder(Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        $objectManagerHelper = new ObjectManagerHelper($this);
        $this->recoverCart = $objectManagerHelper->getObject(
            '\Ebizmarts\SagePaySuite\Observer\RecoverCart',
            [
                'session'        => $this->sessionMock,
                'messageManager' => $this->messageManagerMock,
                'logo'           => $this->logoMock,
                'urlInterface'   => $this->urlInterface,
                'request'        => $this->requestMock
            ]
        );
    }

    public function testExecute()
    {
        $this->requestMock
            ->expects($this->exactly(2))
            ->method('getFrontName')
            ->willReturn(self::TEST_SUCCESS_FRONT_NAME);
        $this->requestMock
            ->expects($this->once())
            ->method('getFullActionName')
            ->willReturn(self::TEST_SUCCESS_ACTION_NAME);

        $this->sessionMock
            ->expects($this->exactly(2))
            ->method('getData')
            ->withConsecutive([SagePaySession::PRESAVED_PENDING_ORDER_KEY], [SagePaySession::CONVERTING_QUOTE_TO_ORDER])
            ->willReturnOnConsecutiveCalls(self::TEST_ORDER_ID, 1);

        $this->urlInterface
            ->expects($this->once())
            ->method('getBaseUrl')
            ->willReturn(self::TEST_BASE_URL);

        $this->messageManagerMock
            ->expects($this->once())
            ->method('addNotice')
            ->with(self::TEST_MESSAGE)
            ->willReturnSelf();

        $this->sessionMock
            ->expects($this->once())
            ->method('setData')
            ->with(
                $this->equalTo(SagePaySession::CONVERTING_QUOTE_TO_ORDER),
                $this->equalTo(0)
            );

        $this->recoverCart->execute($this->observerMock);
    }

    public function testExecuteRecoverCartNotPossible()
    {
        $this->requestMock
            ->expects($this->exactly(2))
            ->method('getFrontName')
            ->willReturn(self::TEST_SUCCESS_FRONT_NAME);
        $this->requestMock
            ->expects($this->once())
            ->method('getFullActionName')
            ->willReturn(self::TEST_SUCCESS_ACTION_NAME);

        $this->sessionMock
            ->expects($this->exactly(2))
            ->method('getData')
            ->withConsecutive([SagePaySession::PRESAVED_PENDING_ORDER_KEY], [SagePaySession::CONVERTING_QUOTE_TO_ORDER])
            ->willReturnOnConsecutiveCalls(null, 0);

        $this->recoverCart->execute($this->observerMock);
    }

    public function testExecuteFilterActionsFalse()
    {
        $this->requestMock
            ->expects($this->once())
            ->method('getFrontName')
            ->willReturn(self::TEST_FAIL_FRONT_NAME);

        $this->recoverCart->execute($this->observerMock);
    }
}
