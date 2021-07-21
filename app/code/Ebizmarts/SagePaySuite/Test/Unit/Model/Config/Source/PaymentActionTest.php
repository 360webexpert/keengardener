<?php
/**
 * Copyright © 2017 ebizmarts. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Ebizmarts\SagePaySuite\Test\Unit\Model\Config\Source;

class PaymentActionTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \Ebizmarts\SagePaySuite\Model\Config\Source\PaymentAction
     */
    private $paymentActionModel;

    // @codingStandardsIgnoreStart
    protected function setUp()
    {
        $objectManagerHelper = new \Magento\Framework\TestFramework\Unit\Helper\ObjectManager($this);
        $this->paymentActionModel = $objectManagerHelper->getObject(
            'Ebizmarts\SagePaySuite\Model\Config\Source\PaymentAction',
            []
        );
    }
    // @codingStandardsIgnoreEnd

    public function testToOptionArray()
    {
        $this->assertEquals(
            [
                'value' => \Ebizmarts\SagePaySuite\Model\Config::ACTION_PAYMENT,
                'label' => __('Payment - Authorize and Capture'),
            ],
            $this->paymentActionModel->toOptionArray()[0]
        );
    }
}
