<?php

namespace Ebizmarts\SagePaySuite\Test\Unit\Block\Form;

class PiTest extends \PHPUnit\Framework\TestCase
{
    private $objectManagerHelper;

    protected function setUp()
    {
        $this->objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
    }

    public function testBlockExists()
    {
        $piPaymentForm = $this->objectManagerHelper->getObject("Ebizmarts\SagePaySuite\Block\Form\Pi");

        $this->assertInstanceOf("\Magento\Payment\Block\Form\Cc", $piPaymentForm);
    }
}
