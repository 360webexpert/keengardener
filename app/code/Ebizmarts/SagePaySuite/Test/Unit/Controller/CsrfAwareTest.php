<?php

namespace Ebizmarts\SagePaySuite\Test\Unit\Controller;

use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\TestFramework\Unit\Helper\ObjectManager as ObjectManagerHelper;

class CsrfAwareTest extends \PHPUnit\Framework\TestCase
{
    /** @var \Magento\Framework\TestFramework\Unit\Helper\ObjectManager */
    private $objectManagerHelper;

    protected function setUp()
    {
        $this->objectManagerHelper = new ObjectManagerHelper($this);
    }

    public function testServerNotify()
    {
        $controller = $this->objectManagerHelper
            ->getObject('Ebizmarts\SagePaySuite\Controller\Server\Notify', []);
        $this->assertInstanceOf(CsrfAwareActionInterface::class, $controller);
    }

    public function testPaypalCallbackNotify()
    {
        $controller = $this->objectManagerHelper
            ->getObject('Ebizmarts\SagePaySuite\Controller\Paypal\Callback', []);
        $this->assertInstanceOf(CsrfAwareActionInterface::class, $controller);
    }

    public function testPaypalCallbackProcessing()
    {
        $controller = $this->objectManagerHelper
            ->getObject('Ebizmarts\SagePaySuite\Controller\Paypal\Processing', []);
        $this->assertInstanceOf(CsrfAwareActionInterface::class, $controller);
    }

    public function testPiCallbackMethods()
    {
        $piCallback3DController = $this->objectManagerHelper
            ->getObject(
                'Ebizmarts\SagePaySuite\Controller\PI\Callback3D',
                []
            );
        $this->assertInstanceOf(CsrfAwareActionInterface::class, $piCallback3DController);
    }
}
